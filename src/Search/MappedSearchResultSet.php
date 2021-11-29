<?php

namespace SIL\Search;

use SearchResult;
use SearchResultSet;

use Title;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class MappedSearchResultSet extends SearchResultSet {

	/**
	 * @var SearchResult[]
	 */
	private $searchMatches = [];

	/**
	 * @var string[]
	 */
	private $termMatches;

	/**
	 * @var integer
	 */
	private $count;

	/**
	 * @since 1.0
	 *
	 * @param SearchResult[] $searchMatches
	 * @param array $termMatches
	 * @param integer $count
	 */
	public function __construct( $searchMatches, $termMatches, $count = 0 ) {
		$this->searchMatches = $searchMatches;
		$this->termMatches = $termMatches;
		$this->count = $count;
	}

	/**
	 * @since 1.0
	 *
	 * @return SearchResult|boolean
	 */
	public function next() {

		if ( $this->searchMatches === false || $this->searchMatches === [] ) {
			return false;
		}

		$key = key( $this->searchMatches );
		$match = current( $this->searchMatches );
		if ( $key !== null && $match !== false ) {
			next( $this->searchMatches );
			if ( $match instanceOf SearchResult ) {
				return $match;
			}

			if ( $match instanceOf Title ) {
				return SearchResult::newFromTitle( $match );
			}
		}

		return false;
	}

	/**
	 * Return number of rows included in this result set.
	 *
	 * @since 1.0
	 *
	 * @return int|void
	 */
	public function numRows() {
		return count( $this->searchMatches );
	}

	/**
	 * Return true if results are included in this result set.
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public function hasResults() {
		return $this->numRows() > 0;
	}

	/**
	 * @since 1.0
	 *
	 * @return integer
	 */
	public function getTotalHits() {
		return $this->count;
	}

	/**
	 * @since 1.0
	 *
	 * @return string[]
	 */
	public function termMatches() {
		return $this->termMatches;
	}

}
