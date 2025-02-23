<?php

namespace SIL\Search;

use SearchResultSet;
use SIL\InterlanguageLinksLookup;

/**
 * @license GPL-2.0-or-later
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
		$mappedMatches = [];

		while ( $searchresult = $matches->next() ) {

			$title = $searchresult->getTitle();

			$pageLanguage = $this->interlanguageLinksLookup->findPageLanguageForTarget( $title );

			if ( $pageLanguage === $languageCode && $this->interlanguageLinksLookup->hasSilAnnotationFor( $title ) ) {
				$mappedMatches[] = $searchresult;
			}
		}

		if ( $mappedMatches === [] ) {
			return null;
		}

		return new MappedSearchResultSet( $mappedMatches, $matches->termMatches() );
	}

}
