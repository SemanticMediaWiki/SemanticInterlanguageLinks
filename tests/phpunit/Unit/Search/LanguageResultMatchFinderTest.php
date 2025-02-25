<?php

namespace SIL\Tests\Search;

use SIL\Search\LanguageResultMatchFinder;
use Title;

/**
 * @covers \SIL\Search\LanguageResultMatchFinder
 *
 * @group semantic-interlanguage-links
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class LanguageResultMatchFinderTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\Search\LanguageResultMatchFinder',
			new LanguageResultMatchFinder( $interlanguageLinksLookup )
		);
	}

	public function testNoMatchResultsToLanguageForNonEmptySearchResultSetThatContainsNullLanguage() {
		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new LanguageResultMatchFinder( $interlanguageLinksLookup );

		$searchResultSet = $this->getMockBuilder( '\SearchResultSet' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertNull(
			$instance->matchResultsToLanguage( $searchResultSet, 'en' )
		);
	}

	public function testNoMatchResultsToLanguageForValidSearchResultSetThatContainsNullLanguage() {
		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new LanguageResultMatchFinder( $interlanguageLinksLookup );

		$searchresult = $this->getMockBuilder( '\SearchResult' )
			->disableOriginalConstructor()
			->getMock();

		$searchresult->expects( $this->once() )
			->method( 'getTitle' )
			->willReturn( Title::newFromText( __METHOD__ ) );

		$searchResultSet = $this->getMockBuilder( '\SearchResultSet' )
			->disableOriginalConstructor()
			->getMock();

		$searchResultSet->expects( $this->any() )
			->method( 'next' )
			->willReturnOnConsecutiveCalls( $searchresult, false );

		$this->assertNull(
			$instance->matchResultsToLanguage( $searchResultSet, 'en' )
		);
	}

	public function testMatchResultsToLanguageForValidSearchResultSet() {
		$title = Title::newFromText( __METHOD__ );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->atLeastOnce() )
			->method( 'hasSilAnnotationFor' )
			->with( $title )
			->willReturn( true );

		$interlanguageLinksLookup->expects( $this->atLeastOnce() )
			->method( 'findPageLanguageForTarget' )
			->with( $title )
			->willReturn( 'mhr' );

		$instance = new LanguageResultMatchFinder( $interlanguageLinksLookup );

		$searchresult = $this->getMockBuilder( '\SearchResult' )
			->disableOriginalConstructor()
			->getMock();

		$searchresult->expects( $this->atLeastOnce() )
			->method( 'getTitle' )
			->willReturn( $title );

		$searchResultSet = $this->getMockBuilder( '\SearchResultSet' )
			->disableOriginalConstructor()
			->getMock();

		$searchResultSet->expects( $this->any() )
			->method( 'next' )
			->willReturnOnConsecutiveCalls( $searchresult, $searchresult, false );

		$this->assertInstanceOf(
			'\SIL\Search\MappedSearchResultSet',
			$instance->matchResultsToLanguage( $searchResultSet, 'mhr' )
		);
	}

}
