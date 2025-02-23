<?php

namespace SIL\Tests\Search;

use SIL\Search\MappedSearchResultSet;

/**
 * @covers \SIL\Search\MappedSearchResultSet
 *
 * @group semantic-interlanguage-links
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class MappedSearchResultSetTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$searchMatches = [];
		$termMatches = [];

		$this->assertInstanceOf(
			'\SIL\Search\MappedSearchResultSet',
			new MappedSearchResultSet( $searchMatches, $termMatches )
		);
	}

	public function testEmptyResulSet() {
		$searchMatches = [];
		$termMatches = [];

		$instance = new MappedSearchResultSet( $searchMatches, $termMatches, 42 );

		$this->assertSame(
			0,
			$instance->numRows()
		);

		$this->assertFalse(
			$instance->hasResults()
		);

		$this->assertEquals(
			42,
			$instance->getTotalHits()
		);

		$this->assertEmpty(
			$instance->termMatches()
		);

		$this->assertFalse(
			$instance->next()
		);
	}

	public function testNextSearchResult() {
		$searchResult = $this->getMockBuilder( '\SearchResult' )
			->disableOriginalConstructor()
			->getMock();

		$fakeTitle = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$searchMatches = [ $searchResult, $fakeTitle, 'Foo' ];
		$termMatches = [];

		$instance = new MappedSearchResultSet( $searchMatches, $termMatches );

		$this->assertEquals(
			$searchResult,
			$instance->next()
		);

		$this->assertInstanceOf(
			'\SearchResult',
			$instance->next()
		);

		$this->assertFalse(
			$instance->next()
		);
	}

}
