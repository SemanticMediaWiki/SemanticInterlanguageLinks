<?php

namespace SIL;

use SMW\DIWikiPage;
use SMW\Cache\FixedInMemoryCache;

use BagOstuff;
use Title;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class LanguageTargetLinksCache {

	/**
	 * Stable cache auxiliary identifier, to be changed in cases where the
	 * cache key needs an auto-update
	 */
	const VERSION = 'jan.2015';

	/**
	 * @var BagOstuff
	 */
	private $cache;

	/**
	 * @var FixedInMemoryCache|null
	 */
	private $inMemoryPageLanguageCache = null;

	/**
	 * @since 1.0
	 *
	 * @param BagOstuff $cache
	 * @param FixedInMemoryCache|null $inMemoryPageLanguageCache
	 */
	public function __construct( BagOstuff $cache, FixedInMemoryCache $inMemoryPageLanguageCache = null ) {
		$this->cache = $cache;
		$this->inMemoryPageLanguageCache = $inMemoryPageLanguageCache;

		if ( $this->inMemoryPageLanguageCache === null ) {
			$this->inMemoryPageLanguageCache = new FixedInMemoryCache( 500 );
		}
	}

	/**
	 * @since 1.0
	 *
	 * @param Title $title
	 *
	 * @param boolean|string
	 */
	public function getPageLanguageFromCache( Title $title ) {

		$pageCacheKey = $this->getPageCacheKey( $title->getPrefixedText() );

		if ( $this->inMemoryPageLanguageCache->contains( $pageCacheKey ) ) {
			return $this->inMemoryPageLanguageCache->fetch( $pageCacheKey );
		}

		$pageLanguage = $this->cache->get(
			$pageCacheKey
		);

		return $pageLanguage;
	}

	/**
	 * @since 1.0
	 *
	 * @param Title $title
	 * @param string $languageCode
	 */
	public function updatePageLanguageToCache( Title $title, $languageCode ) {

		$pageCacheKey = $this->getPageCacheKey( $title->getPrefixedText() );

		$this->inMemoryPageLanguageCache->save(
			$pageCacheKey,
			$languageCode
		);
	}

	/**
	 * @since 1.0
	 *
	 * @param InterlanguageLink $interlanguageLink
	 *
	 * @return boolean|array
	 */
	public function getLanguageTargetLinksFromCache( InterlanguageLink $interlanguageLink ) {

		$cachedLanguageTargetLinks = $this->cache->get(
			$this->getSiteCacheKey( $interlanguageLink->getLinkReference()->getPrefixedText() )
		);

		if ( $interlanguageLink->getLanguageCode() === null ) {
			return $cachedLanguageTargetLinks;
		}

		if ( !isset( $cachedLanguageTargetLinks[ $interlanguageLink->getLanguageCode() ] ) ) {
			return false;
		}

		return $cachedLanguageTargetLinks;
	}

	/**
	 * @since 1.0
	 *
	 * @param InterlanguageLink $interlanguageLink
	 * @param array $languageTargetLinks
	 */
	public function saveLanguageTargetLinksToCache( InterlanguageLink $interlanguageLink, array $languageTargetLinks ) {

		$normalizedLanguageTargetLinks = array();

		foreach ( $languageTargetLinks as $languageCode => $title ) {

			if ( $title instanceof Title ) {
				$title = $title->getPrefixedText();
			}

			$normalizedLanguageTargetLinks[ $languageCode ] = $title;
		}

		if ( $normalizedLanguageTargetLinks === array() ) {
			return;
		}

		$this->cache->set(
			$this->getSiteCacheKey( $interlanguageLink->getLinkReference()->getPrefixedText() ),
			$normalizedLanguageTargetLinks
		);

		foreach ( $normalizedLanguageTargetLinks as $languageCode => $title ) {
			$this->cache->set( $this->getPageCacheKey( $title ), $languageCode );
		}
	}

	/**
	 * @since 1.0
	 *
	 * @param DIWikiPage[] $linkReferences
	 */
	public function deleteLanguageTargetLinksFromCache( array $linkReferences ) {

		foreach ( $linkReferences as $linkReference ) {

			if ( !$linkReference instanceof DIWikiPage ) {
				continue;
			}

			$siteCacheKey = $this->getSiteCacheKey( $linkReference->getTitle()->getPrefixedText() );
			$cachedLanguageTargetLinks = $this->cache->get( $siteCacheKey );

			if ( !is_array( $cachedLanguageTargetLinks ) ) {
				continue;
			}

			foreach ( $cachedLanguageTargetLinks as $cachedLanguageTargetLink ) {
				Title::newFromText( $cachedLanguageTargetLink )->invalidateCache();
			}

			$this->cache->delete( $siteCacheKey );
		}

		return true;
	}

	/**
	 * @since 1.0
	 *
	 * @param Title $title
	 */
	public function deletePageLanguageForTargetFromCache( Title $title ) {

		$pageCacheKey = $this->getPageCacheKey( $title->getPrefixedText() );

		if ( $this->cache->get( $pageCacheKey ) ) {
			$this->cache->delete( $pageCacheKey );
		}

		$this->inMemoryPageLanguageCache->delete(
			$pageCacheKey
		);
	}

	private function getSiteCacheKey( $key ) {
		return $this->getCachePrefix() . ':' . 'sil:' . 's:' . md5( $key . self::VERSION );
	}

	private function getPageCacheKey( $key ) {
		return $this->getCachePrefix() . ':' . 'sil:' . 'p:' . md5( $key . self::VERSION );
	}

	private function getCachePrefix() {
		return $GLOBALS['wgCachePrefix'] === false ? wfWikiID() : $GLOBALS['wgCachePrefix'];
	}

}
