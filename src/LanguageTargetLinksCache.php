<?php

namespace SIL;

use Onoi\Cache\Cache;

use SMW\DIWikiPage;

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
	const VERSION = '20150122';

	/**
	 * @var Cache
	 */
	private $cache;

	/**
	 * @var string|null
	 */
	private $cachePrefix = null;

	/**
	 * The current cache strategy is to store language by page into a blob value
	 * to avoid high cache fragmentation and keep the cache lookup performant
	 * on generated category/search lists.
	 *
	 * Whether the blob language-page strategy has a considerable performance draw
	 * back on large lists of stored language-page pairs has yet to be determined
	 * but it will be fairly easy to switch to a single language-page strategy
	 * if necessary.
	 *
	 * @var string
	 */
	private $pageLanguageCacheStrategy = 'blob';

	/**
	 * @since 1.0
	 *
	 * @param Cache $cache
	 */
	public function __construct( Cache $cache ) {
		$this->cache = $cache;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $cachePrefix
	 */
	public function setCachePrefix( $cachePrefix ) {
		$this->cachePrefix = $cachePrefix;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $pageLanguageCacheStrategy
	 */
	public function setPageLanguageCacheStrategy( $pageLanguageCacheStrategy ) {
		$this->pageLanguageCacheStrategy = $pageLanguageCacheStrategy;
	}

	/**
	 * @since 1.0
	 *
	 * @param Title $title
	 *
	 * @param boolean|string
	 */
	public function getPageLanguageFromCache( Title $title ) {
		return $this->fetch( $title );
	}

	/**
	 * @since 1.0
	 *
	 * @param Title $title
	 * @param string $languageCode
	 */
	public function updatePageLanguageToCache( Title $title, $languageCode ) {

		$normalizedLanguageTargetLink = array(
			$languageCode => $title->getPrefixedText()
		);

		$this->save(
			$title,
			$normalizedLanguageTargetLink
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

		$cachedLanguageTargetLinks = $this->cache->fetch(
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

		$this->cache->save(
			$this->getSiteCacheKey( $interlanguageLink->getLinkReference()->getPrefixedText() ),
			$normalizedLanguageTargetLinks
		);

		$this->save(
			$interlanguageLink->getLinkReference(),
			$normalizedLanguageTargetLinks
		);
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
			$cachedLanguageTargetLinks = $this->cache->fetch( $siteCacheKey );

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
		$this->delete( $title );
	}

	private function fetch( Title $title ) {

		$pageCacheKey = $this->getPageCacheKey(
			$title->getPrefixedText()
		);

		if ( $this->pageLanguageCacheStrategy !== 'blob' ) {
			return $this->cache->fetch( $pageCacheKey );
		}

		$pageLanguageCacheBlob = $this->getPageLanguageCacheBlob();

		return isset( $pageLanguageCacheBlob[ $pageCacheKey ] ) ? $pageLanguageCacheBlob[ $pageCacheKey ] : false;
	}

	private function save( Title $title, array $normalizedLanguageTargetLinks ) {

		if ( $this->pageLanguageCacheStrategy !== 'blob' ) {

			foreach ( $normalizedLanguageTargetLinks as $languageCode => $target ) {
				$this->cache->save( $this->getPageCacheKey( $target ), $languageCode );
			}

			return;
		}

		$pageLanguageCacheBlob = $this->getPageLanguageCacheBlob();

		foreach ( $normalizedLanguageTargetLinks as $languageCode => $target ) {
			$pageLanguageCacheBlob[ $this->getPageCacheKey( $target ) ] = $languageCode;
		}

		$this->cache->save(
			$this->getPageLanguageCacheBlobKey(),
			$pageLanguageCacheBlob
		);
	}

	private function delete( Title $title ) {

		if ( $this->pageLanguageCacheStrategy !== 'blob' ) {
			return $this->cache->delete( $this->getPageCacheKey( $title->getPrefixedText() )	);
		}

		$pageLanguageCacheBlob = $this->getPageLanguageCacheBlob();
		unset( $pageLanguageCacheBlob[ $this->getPageCacheKey( $title->getPrefixedText() ) ] );

		$this->cache->save(
			$this->getPageLanguageCacheBlobKey(),
			$pageLanguageCacheBlob
		);
	}

	private function getPageLanguageCacheBlob() {

		$pageLanguageCacheBlob = $this->cache->fetch( $this->getPageLanguageCacheBlobKey() );

		if ( $pageLanguageCacheBlob === false ) {
			$pageLanguageCacheBlob = array();
		}

		return $pageLanguageCacheBlob;
	}

	private function getSiteCacheKey( $key ) {
		return $this->getCachePrefix() . 's:' . md5( $key . self::VERSION );
	}

	private function getPageLanguageCacheBlobKey( $key = '' ) {
		return $this->getCachePrefix() . 'b:' . md5( $key . self::VERSION );
	}

	private function getPageCacheKey( $key ) {
		return $this->getCachePrefix() . 'p:' . md5( $key . ( $this->pageLanguageCacheStrategy !== 'blob' ? self::VERSION : '' ) );
	}

	private function getCachePrefix() {

		if ( $this->cachePrefix === null ) {
			$this->cachePrefix = ( $GLOBALS['wgCachePrefix'] === false ? wfWikiID() : $GLOBALS['wgCachePrefix'] ) . ':' . 'sil:';
		}

		return $this->cachePrefix;
	}

}
