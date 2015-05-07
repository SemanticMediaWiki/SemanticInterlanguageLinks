<?php

namespace SIL\Search;

use SIL\InterlanguageLinksLookup;

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
	 * @return MappedSearchResultSet|null
	 */
	public function matchResultsToLanguage( SearchResultSet $matches, $languageCode ) {

		$mappedMatches = array();

		while ( $searchresult = $matches->next() ) {

			$title = $searchresult->getTitle();

			$pageLanguage = $this->interlanguageLinksLookup->findPageLanguageForTarget( $title );

			if ( $pageLanguage === $languageCode ) {
				$mappedMatches[] = $searchresult;
			}
		}

		if ( $mappedMatches === array() ) {
			return null;
		}

		return new MappedSearchResultSet( $mappedMatches, $matches->termMatches() );
	}

}
