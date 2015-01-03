<?php

namespace SIL;

use SMW\DIWikiPage;

use BagOstuff;
use Title;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CachedLanguageTargetLinks {

	const VERSION = 'dec.2014';

	/**
	 * @var BagOstuff
	 */
	private $cache;

	/**
	 * @since 1.0
	 *
	 * @param BagOstuff $cache
	 */
	public function __construct( BagOstuff $cache ) {
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
		return $this->cache->get(
			$this->getPageCacheKey( $title->getPrefixedText() )
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
	}

	private function getSiteCacheKey( $title ) {
		return $this->getCachePrefix() . ':' . 'sil:' . 's:' . md5( $title . self::VERSION );
	}

	private function getPageCacheKey( $title ) {
		return $this->getCachePrefix() . ':' . 'sil:' . 'p:' . md5( $title . self::VERSION );
	}

	private function getCachePrefix() {
		return $GLOBALS['wgCachePrefix'] === false ? wfWikiID() : $GLOBALS['wgCachePrefix'];
	}

}
