<?php

use SIL\HookRegistry;
use SIL\CacheKeyProvider;
use SMW\ApplicationFactory;
use Onoi\Cache\CacheFactory;

/**
 * @see https://github.com/SemanticMediaWiki/SemanticInterlanguageLinks/
 *
 * @defgroup SIL Semantic Interlanguage Links
 */
SemanticInterlanguageLinks::load();

/**
 * @codeCoverageIgnore
 */
class SemanticInterlanguageLinks {

	/**
	 * @since 1.4
	 *
	 * @note It is expected that this function is loaded before LocalSettings.php
	 * to ensure that settings and global functions are available by the time
	 * the extension is activated.
	 */
	public static function load() {

		if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
			include_once __DIR__ . '/vendor/autoload.php';
		}

		// Load DefaultSettings
		require_once __DIR__ . '/DefaultSettings.php';
	}

	/**
	 * @since 1.3
	 */
	public static function initExtension( $credits = [] ) {

		// See https://phabricator.wikimedia.org/T151136
		define( 'SIL_VERSION', isset( $credits['version'] ) ? $credits['version'] : 'UNKNOWN' );

		// Register message files
		$GLOBALS['wgMessagesDirs']['SemanticInterlanguageLinks'] = __DIR__ . '/i18n';
		$GLOBALS['wgExtensionMessagesFiles']['SemanticInterlanguageLinksMagic'] = __DIR__ . '/i18n/SemanticInterlanguageLinks.magic.php';

		$GLOBALS['wgHooks']['SMW::Config::BeforeCompletion'][] = '\SIL\HookRegistry::onBeforeConfigCompletion';
	}

	/**
	 * @since 1.3
	 */
	public static function onExtensionFunction() {

		if ( !defined( 'SMW_VERSION' ) ) {
			if ( PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg' ) {
				die( "\nThe 'Semantic Interlanguage Links' extension requires 'Semantic MediaWiki' to be installed and enabled.\n" );
			} else {
				die( '<b>Error:</b> The <a href="https://github.com/SemanticMediaWiki/SemanticInterlanguageLinks/">Semantic Interlanguage Links</a> extension requires <a href="https://www.semantic-mediawiki.org/wiki/Semantic_MediaWiki">Semantic MediaWiki</a> to be installed and enabled.<br />' );
			}
		}

		// Legacy
		if ( isset( $GLOBALS['egSILEnabledCategoryFilterByLanguage'] ) ) {
			$GLOBALS['silgEnabledCategoryFilterByLanguage'] = $GLOBALS['egSILEnabledCategoryFilterByLanguage'];
		}

		if ( isset( $GLOBALS['egSILCacheType'] ) ) {
			$GLOBALS['silgCacheType'] = $GLOBALS['egSILCacheType'];
		}

		$cacheFactory = new CacheFactory();

		$compositeCache = $cacheFactory->newCompositeCache( [
			$cacheFactory->newFixedInMemoryLruCache( 500 ),
			$cacheFactory->newMediaWikiCache( ObjectCache::getInstance( $GLOBALS['silgCacheType'] ) )
		] );

		$cacheKeyProvider = new CacheKeyProvider(
			$GLOBALS['wgCachePrefix'] === false ? wfWikiID() : $GLOBALS['wgCachePrefix']
		);

		$hookRegistry = new HookRegistry(
			ApplicationFactory::getInstance()->getStore(),
			$compositeCache,
			$cacheKeyProvider
		);

		$hookRegistry->register();
	}

}
