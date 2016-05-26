<?php

namespace SIL;

use Onoi\Cache\Cache;
use SMW\Store;
use SMW\DIWikiPage;
use SMW\DIProperty;

use Title;

/**
 * Modifies the content language based on the SIL annotation found
 * for the selected page.
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class PageContentLanguageModifier {

	const POOLCACHE_ID = 'sil.pagecontentlanguage';

	/**
	 * @var InterlanguageLinksLookup
	 */
	private $interlanguageLinksLookup;

	/**
	 * @var Cache
	 */
	private $intermediaryCache;

	/**
	 * @since 1.0
	 *
	 * @param InterlanguageLinksLookup $interlanguageLinksLookup
	 * @param Cache $intermediaryCache
	 */
	public function __construct( InterlanguageLinksLookup $interlanguageLinksLookup, Cache $intermediaryCache ) {
		$this->interlanguageLinksLookup = $interlanguageLinksLookup;
		$this->intermediaryCache = $intermediaryCache;
	}

	/**
	 * @since 1.3
	 *
	 * @param Title $title
	 * @param string &languageCode
	 */
	public function addToIntermediaryCache( Title $title, $languageCode ) {
		$this->intermediaryCache->save( $this->getHashFrom( $title ), $languageCode );
	}

	/**
	 * @since 1.0
	 *
	 * @param Title $title
	 * @param Language|string &$pageLanguage
	 *
	 * @return string
	 */
	public function getPageContentLanguage( Title $title, $pageLanguage ) {

		$hash = $this->getHashFrom( $title );

		if ( ( $cachedLanguageCode = $this->intermediaryCache->fetch( $hash ) ) ) {
			return $cachedLanguageCode;
		}

		$lookupLanguageCode = $this->interlanguageLinksLookup->findPageLanguageForTarget( $title );

		if ( $lookupLanguageCode !== null && $lookupLanguageCode !== '' ) {
			$pageLanguage = $lookupLanguageCode;
		}

		if ( $pageLanguage instanceof \Language ) {
			$pageLanguage = $pageLanguage->getCode();
		}

		$this->intermediaryCache->save( $hash, $pageLanguage );

		return $pageLanguage;
	}

	private function getHashFrom( Title $title ) {
		return md5( $title->getPrefixedText() );
	}

}
