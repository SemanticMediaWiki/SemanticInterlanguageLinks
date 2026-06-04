<?php

namespace SIL;

use MediaWiki\WikiMap\WikiMap;
use ObjectCache;
use Onoi\Cache\CacheFactory;
use SMW\Services\ServicesFactory as ApplicationFactory;

/**
 * @license GPL-2.0-or-later
 * @since 1.3
 *
 * @codeCoverageIgnore
 */
class Setup {

	/**
	 * @since 1.3
	 */
	public static function onExtensionFunction() {
		$cacheFactory = new CacheFactory();

		$compositeCache = $cacheFactory->newCompositeCache( [
			$cacheFactory->newFixedInMemoryLruCache( 500 ),
			$cacheFactory->newMediaWikiCache( ObjectCache::getInstance( $GLOBALS['silgCacheType'] ) )
		] );

		$cacheKeyProvider = new CacheKeyProvider(
			$GLOBALS['wgCachePrefix'] === false ? WikiMap::getCurrentWikiId() : $GLOBALS['wgCachePrefix']
		);

		$hookRegistry = new HookRegistry(
			ApplicationFactory::getInstance()->getStore(),
			$compositeCache,
			$cacheKeyProvider
		);

		$hookRegistry->register();
	}

}
