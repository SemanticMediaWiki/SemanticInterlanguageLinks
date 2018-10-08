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
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of the Semantic Interlanguage Links extension. It is not a valid entry point.' );
}

if ( defined( 'SIL_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

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

		// Load DefaultSettings
		require_once __DIR__ . '/DefaultSettings.php';

		if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
			include_once __DIR__ . '/vendor/autoload.php';
		}

		/**
		 * In case extension.json is being used, the succeeding steps are
		 * expected to be handled by the ExtensionRegistry aka extension.json
		 * ...
		 *
		 * 	"callback": "SemanticInterlanguageLinks::initExtension",
		 * 	"ExtensionFunctions": [
		 * 		"SemanticInterlanguageLinks::onExtensionFunction"
		 * 	],
		 */
		self::initExtension();

		$GLOBALS['wgExtensionFunctions'][] = function() {
			self::onExtensionFunction();
		};
	}

	/**
	 * @since 1.3
	 */
	public static function initExtension() {

		define( 'SIL_VERSION', '1.5.0' );

		// Register extension info
		$GLOBALS[ 'wgExtensionCredits' ][ 'semantic' ][ ] = [
			'path'           => __FILE__,
			'name'           => 'Semantic Interlanguage Links',
			'author'         => [ 'James Hong Kong' ],
			'url'            => 'https://github.com/SemanticMediaWiki/SemanticInterlanguageLinks/',
			'descriptionmsg' => 'sil-desc',
			'version'        => SIL_VERSION,
			'license-name'   => 'GPL-2.0-or-later',
		];

		// Register message files
		$GLOBALS['wgMessagesDirs']['SemanticInterlanguageLinks'] = __DIR__ . '/i18n';
		$GLOBALS['wgExtensionMessagesFiles']['SemanticInterlanguageLinksMagic'] = __DIR__ . '/i18n/SemanticInterlanguageLinks.magic.php';

		self::onBeforeExtensionFunction();
	}

	/**
	 * Register hooks that require to be listed as soon as possible and preferable
	 * before the execution of onExtensionFunction
	 *
	 * @since 1.4
	 */
	public static function onBeforeExtensionFunction() {
		$GLOBALS['wgHooks']['SMW::Config::BeforeCompletion'][] = '\SIL\HookRegistry::onBeforeConfigCompletion';
	}

	/**
	 * @since 1.4
	 */
	public static function checkRequirements() {

		if ( version_compare( $GLOBALS[ 'wgVersion' ], '1.27', 'lt' ) ) {
			die( '<b>Error:</b> This version of <a href="https://github.com/SemanticMediaWiki/SemanticInterlanguageLinks/">Semantic Interlanguage Links</a> is only compatible with MediaWiki 1.27 or above. You need to upgrade MediaWiki first.' );
		}

		if ( !defined( 'SMW_VERSION' ) ) {
			die( '<b>Error:</b> <a href="https://github.com/SemanticMediaWiki/SemanticInterlanguageLinks/">Semantic Interlanguage Links</a> requires <a href="https://github.com/SemanticMediaWiki/SemanticMediaWiki/">Semantic MediaWiki</a>. Please enable or install the extension first.' );
		}
	}

	/**
	 * @since 1.3
	 */
	public static function onExtensionFunction() {

		// Check requirements after LocalSetting.php has been processed
		self::checkRequirements();

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

	/**
	 * @since 1.3
	 *
	 * @return string|null
	 */
	public static function getVersion() {
		return SIL_VERSION;
	}

}
