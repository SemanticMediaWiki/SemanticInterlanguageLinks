<?php

namespace SIL;

use MediaWiki\Title\Title;
use Wikimedia\ObjectCache\BagOStuff;

/**
 * Modifies the content language based on the SIL annotation found
 * for the selected page.
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class PageContentLanguageOnTheFlyModifier {

	/**
	 * @var InterlanguageLinksLookup
	 */
	private $interlanguageLinksLookup;

	/**
	 * @var BagOStuff
	 */
	private $intermediaryCache;

	/**
	 * @since 1.0
	 *
	 * @param InterlanguageLinksLookup $interlanguageLinksLookup
	 * @param BagOStuff $intermediaryCache
	 */
	public function __construct( InterlanguageLinksLookup $interlanguageLinksLookup, BagOStuff $intermediaryCache ) {
		$this->interlanguageLinksLookup = $interlanguageLinksLookup;
		$this->intermediaryCache = $intermediaryCache;
	}

	/**
	 * @since 1.3
	 *
	 * @param Title $title
	 * @param string $languageCode
	 */
	public function addToIntermediaryCache( Title $title, $languageCode ) {
		$this->intermediaryCache->set( $this->getHashFrom( $title ), $languageCode );
	}

	/**
	 * @since 1.0
	 *
	 * @param Title $title
	 * @param Language|string $pageLanguage
	 *
	 * @return string
	 */
	public function getPageContentLanguage( Title $title, $pageLanguage ) {
		$hash = $this->getHashFrom( $title );

		// Convert language codes from BCP 47 to lowercase to ensure that codes
		// are matchable against `LanguageNameUtils->getLanguageNames` for languages like
		// zh-Hans etc.
		if ( ( $cachedLanguageCode = $this->intermediaryCache->get( $hash ) ) ) {
			return strtolower( $cachedLanguageCode );
		}

		$lookupLanguageCode = $this->interlanguageLinksLookup->findPageLanguageForTarget( $title );

		if ( $lookupLanguageCode !== null && $lookupLanguageCode !== '' ) {
			$pageLanguage = $lookupLanguageCode;
		}

		if ( $pageLanguage instanceof \Language ) {
			$pageLanguage = $pageLanguage->getCode();
		}

		$pageLanguage = strtolower( $pageLanguage );

		$this->intermediaryCache->set( $hash, $pageLanguage );

		return $pageLanguage;
	}

	private function getHashFrom( Title $title ) {
		return md5( $title->getPrefixedText() ?? '' );
	}

}
