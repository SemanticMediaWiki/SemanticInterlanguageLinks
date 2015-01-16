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
	const VERSION = '2015.01.16';

	/**
	 * @var BagOstuff
	 */
	private $cache;

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
	 * @param Title $title
	 *
	 * @param boolean|string
	 */
	public function getPageLanguageFromCache( Title $title ) {

		$pageCacheKey = $this->getPageCacheKey( $title->getPrefixedText() );

		$pageLanguage = $this->cache->fetch(
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

		$this->cache->save(
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

		foreach ( $normalizedLanguageTargetLinks as $languageCode => $title ) {
			$this->cache->save( $this->getPageCacheKey( $title ), $languageCode );
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

		$pageCacheKey = $this->getPageCacheKey( $title->getPrefixedText() );

		$this->cache->delete(
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
