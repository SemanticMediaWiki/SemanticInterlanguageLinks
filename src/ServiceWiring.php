<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\WikiMap\WikiMap;
use SIL\CacheKeyProvider;
use SIL\InterlanguageLinksLookup;
use SIL\LanguageTargetLinksCache;
use SIL\PageContentLanguageOnTheFlyModifier;
use SMW\Services\ServicesFactory;
use Wikimedia\ObjectCache\BagOStuff;
use Wikimedia\ObjectCache\CachedBagOStuff;
use Wikimedia\ObjectCache\HashBagOStuff;

/**
 * @license GPL-2.0-or-later
 */
return [

	'SIL.CacheKeyProvider' => static function ( MediaWikiServices $services ): CacheKeyProvider {
		return new CacheKeyProvider(
			$GLOBALS['wgCachePrefix'] === false ? WikiMap::getCurrentWikiId() : $GLOBALS['wgCachePrefix']
		);
	},

	'SIL.ObjectCache' => static function ( MediaWikiServices $services ): BagOStuff {
		// A process-local cache tier (bounded to 500 entries) over the
		// configured persistent object cache.
		return new CachedBagOStuff(
			$services->getObjectCacheFactory()->getInstance( $GLOBALS['silgCacheType'] ),
			[ 'maxKeys' => 500 ]
		);
	},

	'SIL.InterlanguageLinksLookup' => static function ( MediaWikiServices $services ): InterlanguageLinksLookup {
		$languageTargetLinksCache = new LanguageTargetLinksCache(
			$services->getService( 'SIL.ObjectCache' ),
			$services->getService( 'SIL.CacheKeyProvider' )
		);

		$interlanguageLinksLookup = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$interlanguageLinksLookup->setStore( ServicesFactory::getInstance()->getStore() );

		return $interlanguageLinksLookup;
	},

	'SIL.PageContentLanguageOnTheFlyModifier' => static function ( MediaWikiServices $services ): PageContentLanguageOnTheFlyModifier {
		return new PageContentLanguageOnTheFlyModifier(
			$services->getService( 'SIL.InterlanguageLinksLookup' ),
			new HashBagOStuff( [ 'maxKeys' => 500 ] )
		);
	},

];
