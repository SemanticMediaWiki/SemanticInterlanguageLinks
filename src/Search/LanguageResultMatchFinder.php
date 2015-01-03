<?php

namespace SIL\Search;

use SIL\InterlanguageLinksLookup;
use SMW\Cache\FixedInMemoryCache;

use SearchResultSet;
use Title;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class LanguageResultMatchFinder {

	/**
	 * @var InterlanguageLinksLookup|null
	 */
	private $interlanguageLinksLookup = null;

	/**
	 * @var FixedInMemoryCache|null
	 */
	private static $inMemoryPageLanguageCache = null;

	/**
	 * @since 1.0
	 *
	 * @param InterlanguageLinksLookup $interlanguageLinksLookup
	 */
	public function __construct( InterlanguageLinksLookup $interlanguageLinksLookup ) {
		$this->interlanguageLinksLookup = $interlanguageLinksLookup;
	}

	/**
	 * @since 1.0
	 *
	 * @param SearchResultSet $matches
	 * @param $languageCode
	 *
	 * @return MappedSearchResultSet|boolean
	 */
	public function matchResultsToLanguage( SearchResultSet $matches, $languageCode ) {

		$mappedMatches = array();

		while ( $searchresult = $matches->next() ) {

			$title = $searchresult->getTitle();

			$pageLanguage = $this->findPageLanguageForTarget( $title );

			if ( $pageLanguage === $languageCode ) {
				$mappedMatches[] = $searchresult;
			}

			$this->getInMemoryPageLanguageCache()->save(
				$title->getPrefixedDBKey(),
				$pageLanguage
			);
		}

		if ( $mappedMatches === array() ) {
			return false;
		}

		return new MappedSearchResultSet( $mappedMatches, $matches->termMatches() );
	}

	private function findPageLanguageForTarget( Title $title ) {

		$pageLanguage = $this->tryPageLanguageFromInMemoryCache( $title );

		if ( $pageLanguage === false ) {
			$pageLanguage = $this->interlanguageLinksLookup->tryCachedPageLanguageForTarget( $title );
		}

		if( $pageLanguage === false ) {
			$pageLanguage = $this->interlanguageLinksLookup->findLastPageLanguageForTarget( $title );
		}

		return $pageLanguage;
	}

	private function tryPageLanguageFromInMemoryCache( Title $title ) {

		if ( !$this->getInMemoryPageLanguageCache()->contains( $title->getPrefixedDBKey() ) ) {
			return false;
		}

		return $this->getInMemoryPageLanguageCache()->fetch( $title->getPrefixedDBKey() );
	}

	private function getInMemoryPageLanguageCache() {

		if ( self::$inMemoryPageLanguageCache === null ) {
			self::$inMemoryPageLanguageCache = new FixedInMemoryCache( 500 );
		}

		return self::$inMemoryPageLanguageCache;
	}

}
