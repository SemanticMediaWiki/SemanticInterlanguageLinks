<?php

namespace SIL;

use SMW\Store;
use SMW\DIWikiPage;
use SMW\DIProperty;
use SMW\Cache\FixedInMemoryCache;

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

	/**
	 * @var InterlanguageLinksLookup
	 */
	private $interlanguageLinksLookup;

	/**
	 * @var Title
	 */
	private $title;

	/**
	 * @var FixedInMemoryCache|null
	 */
	private static $inMemoryPageLanguageCache = null;

	/**
	 * @since 1.0
	 *
	 * @param InterlanguageLinksLookup $interlanguageLinksLookup
	 * @param Title $title
	 */
	public function __construct( InterlanguageLinksLookup $interlanguageLinksLookup, Title $title ) {
		$this->interlanguageLinksLookup = $interlanguageLinksLookup;
		$this->title = $title;
	}
	/**
	 * @since 1.0
	 */
	public function clear() {
		self::$inMemoryPageLanguageCache = null;
	}

	/**
	 * @since 1.0
	 *
	 * @param Title $title
	 * @param $languageCode
	 *
	 * @return string
	 */
	public function addLanguageToInMemoryCache( Title $title, $languageCode ) {
		$this->getInMemoryPageLanguageCache()->save( $title->getPrefixedDBKey(), $languageCode );
		return $languageCode;
	}

	/**
	 * @since 1.0
	 *
	 * @param Language|string &$pageLanguage
	 *
	 * @return boolean
	 */
	public function modifyLanguage( &$pageLanguage ) {

		if ( $this->tryPageLanguageFromInMemoryCache( $pageLanguage ) ) {
			return true;
		}

		$lookupLanguageCode = $this->interlanguageLinksLookup->findLastPageLanguageForTarget( $this->title );

		$this->addLanguageToInMemoryCache( $this->title, $lookupLanguageCode );

		if ( $lookupLanguageCode === null || $lookupLanguageCode === '' ) {
			return true;
		}

		$pageLanguage = $lookupLanguageCode;

		return true;
	}

	private function tryPageLanguageFromInMemoryCache( &$pageLanguage ) {

		if ( !$this->getInMemoryPageLanguageCache()->contains( $this->title->getPrefixedDBKey() ) ) {
			return false;
		}

		$cachedLanguageCode = $this->getInMemoryPageLanguageCache()->fetch( $this->title->getPrefixedDBKey() );

		if ( $cachedLanguageCode !== '' ) {
			$pageLanguage = $cachedLanguageCode;
		}

		return true;
	}

	private function getInMemoryPageLanguageCache() {

		// Use the FixedInMemoryCache to ensure that during a job run the array is not hit by any
		// memory leak and limited to a fixed size
		if ( self::$inMemoryPageLanguageCache === null ) {
			self::$inMemoryPageLanguageCache = new FixedInMemoryCache( 50 );
		}

		return self::$inMemoryPageLanguageCache;
	}

}
