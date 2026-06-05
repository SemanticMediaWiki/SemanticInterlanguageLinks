<?php

namespace SIL;

use MediaWiki\MediaWikiServices;
use MediaWiki\WikiMap\WikiMap;
use SMW\Services\ServicesFactory as ApplicationFactory;
use Wikimedia\ObjectCache\CachedBagOStuff;

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
		$objectCacheFactory = MediaWikiServices::getInstance()->getObjectCacheFactory();

		// A process-local cache tier (bounded to 500 entries) over the
		// configured persistent object cache, mirroring the previous composite.
		$cache = new CachedBagOStuff(
			$objectCacheFactory->getInstance( $GLOBALS['silgCacheType'] ),
			[ 'maxKeys' => 500 ]
		);

		$cacheKeyProvider = new CacheKeyProvider(
			$GLOBALS['wgCachePrefix'] === false ? WikiMap::getCurrentWikiId() : $GLOBALS['wgCachePrefix']
		);

		$hookRegistry = new HookRegistry(
			ApplicationFactory::getInstance()->getStore(),
			$cache,
			$cacheKeyProvider
		);

		$hookRegistry->register();
	}

}
