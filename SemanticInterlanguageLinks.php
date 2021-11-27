<?php

use SIL\HookRegistry;
use SIL\CacheKeyProvider;
use SMW\ApplicationFactory;
use Onoi\Cache\CacheFactory;

/**
 * @codeCoverageIgnore
 */
class SemanticInterlanguageLinks {

	/**
	 * @since 1.3
	 */
	public static function initExtension( $credits = [] ) {

		// Load DefaultSettings
		require_once __DIR__ . '/DefaultSettings.php';

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
