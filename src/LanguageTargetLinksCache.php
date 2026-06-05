<?php

namespace SIL;

use MediaWiki\Title\Title;
use SMW\DataItems\WikiPage;
use Wikimedia\ObjectCache\BagOStuff;

/**
 * To make a page view responsive and avoid a repetitive or exhausting query
 * process, this class is expected to cache all objects necessary and be
 * accessible through the `InterlanguageLinksLookup` class.
 *
 * It is expected that the cache uses a "Composite" approach in order for short-lived
 * requests to be stored in-memory while other information are stored on a
 * persistence layer to increase lookup performance for succeeding requests.
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class LanguageTargetLinksCache {

	/**
	 * @var BagOStuff
	 */
	private $cache;

	/**
	 * @var CacheKeyProvider
	 */
	private $cacheKeyProvider;

	/**
	 * The current page language cache strategy is to store language by page
	 * into a blob value to avoid high cache fragmentation and keep the cache
	 * lookup performant on generated category/search lists.
	 *
	 * Whether the blob language-page strategy has a considerable performance draw
	 * back on large lists of stored language-page pairs has yet to be determined
	 * but it will be fairly easy to switch to a single language-page strategy
	 * if necessary.
	 *
	 * @var string
	 */
	private $pageLanguageCacheStrategy = 'single';

	/**
	 * @since 1.0
	 *
	 * @param BagOStuff $cache
	 * @param CacheKeyProvider $cacheKeyProvider
	 */
	public function __construct( BagOStuff $cache, CacheKeyProvider $cacheKeyProvider ) {
		$this->cache = $cache;
		$this->cacheKeyProvider = $cacheKeyProvider;
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
	 * @return bool|string
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
	public function pushPageLanguageToCache( Title $title, $languageCode ) {
		$normalizedLanguageTargetLink = [
			$languageCode => $title->getPrefixedText()
		];

		$this->save( $normalizedLanguageTargetLink );
	}

	/**
	 * @since 1.0
	 *
	 * @param InterlanguageLink $interlanguageLink
	 *
	 * @return bool|array
	 */
	public function getLanguageTargetLinksFromCache( InterlanguageLink $interlanguageLink ) {
		// Call to a member function getPrefixedText() on null in ...SemanticInterlanguageLinks/src/LanguageTargetLinksCache.php on line 105
		if ( $interlanguageLink->getLinkReference() === null ) {
			return false;
		}

		$cachedLanguageTargetLinks = $this->cache->get(
			$this->cacheKeyProvider->getSiteCacheKey( $interlanguageLink->getLinkReference()->getPrefixedText() )
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
		$normalizedLanguageTargetLinks = [];

		foreach ( $languageTargetLinks as $languageCode => $title ) {

			if ( $title instanceof Title ) {
				$title = $title->getPrefixedText();
			}

			$normalizedLanguageTargetLinks[ $languageCode ] = $title;
		}

		if ( $normalizedLanguageTargetLinks === [] ) {
			return;
		}

		$this->cache->set(
			$this->cacheKeyProvider->getSiteCacheKey( $interlanguageLink->getLinkReference()->getPrefixedText() ),
			$normalizedLanguageTargetLinks
		);

		$this->save( $normalizedLanguageTargetLinks );
	}

	/**
	 * @since 1.0
	 *
	 * @param WikiPage[] $linkReferences
	 */
	public function deleteLanguageTargetLinksFromCache( array $linkReferences ) {
		foreach ( $linkReferences as $linkReference ) {

			if ( !$linkReference instanceof WikiPage ) {
				continue;
			}

			$siteCacheKey = $this->cacheKeyProvider->getSiteCacheKey(
				$linkReference->getTitle()->getPrefixedText()
			);

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
		$this->delete( $title );
	}

	private function fetch( Title $title ) {
		$pageCacheKey = $this->cacheKeyProvider->getPageCacheKey(
			$title->getPrefixedText(),
			$this->pageLanguageCacheStrategy === 'blob'
		);

		if ( $this->pageLanguageCacheStrategy !== 'blob' ) {
			return $this->cache->get( $pageCacheKey );
		}

		$pageLanguageCacheBlob = $this->getPageLanguageCacheBlob();

		return isset( $pageLanguageCacheBlob[ $pageCacheKey ] ) ? $pageLanguageCacheBlob[ $pageCacheKey ] : false;
	}

	private function save( array $normalizedLanguageTargetLinks ) {
		if ( $this->pageLanguageCacheStrategy !== 'blob' ) {

			foreach ( $normalizedLanguageTargetLinks as $languageCode => $target ) {
				$this->cache->set(
					$this->cacheKeyProvider->getPageCacheKey( $target, false ),
					$languageCode
				);
			}

			return;
		}

		$pageLanguageCacheBlob = $this->getPageLanguageCacheBlob();

		foreach ( $normalizedLanguageTargetLinks as $languageCode => $target ) {
			$pageLanguageCacheBlob[ $this->cacheKeyProvider->getPageCacheKey( $target, true ) ] = $languageCode;
		}

		$this->cache->set(
			$this->cacheKeyProvider->getPageLanguageCacheBlobKey(),
			$pageLanguageCacheBlob
		);
	}

	private function delete( Title $title ) {
		$this->cache->delete(
			$this->cacheKeyProvider->getSiteCacheKey( $title->getPrefixedText() )
		);

		if ( $this->pageLanguageCacheStrategy !== 'blob' ) {
			return $this->cache->delete(
				$this->cacheKeyProvider->getPageCacheKey( $title->getPrefixedText(), false )
			);
		}

		$pageLanguageCacheBlob = $this->getPageLanguageCacheBlob();
		unset( $pageLanguageCacheBlob[ $this->cacheKeyProvider->getPageCacheKey( $title->getPrefixedText(), true ) ] );

		$this->cache->set(
			$this->cacheKeyProvider->getPageLanguageCacheBlobKey(),
			$pageLanguageCacheBlob
		);
	}

	private function getPageLanguageCacheBlob() {
		$pageLanguageCacheBlob = $this->cache->get(
			$this->cacheKeyProvider->getPageLanguageCacheBlobKey()
		);

		if ( $pageLanguageCacheBlob === false ) {
			$pageLanguageCacheBlob = [];
		}

		return $pageLanguageCacheBlob;
	}

}
