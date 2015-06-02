<?php

namespace SIL\Tests\Search;

use SIL\Search\LanguageResultMatchFinder;

use Title;

/**
 * @covers \SIL\Search\LanguageResultMatchFinder
 *
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class LanguageResultMatchFinderTest extends \PHPUnit_Framework_TestCase {

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
			->will( $this->returnValue( Title::newFromText( __METHOD__ ) ) );

		$searchResultSet = $this->getMockBuilder( '\SearchResultSet' )
			->disableOriginalConstructor()
			->getMock();

		$searchResultSet->expects( $this->any() )
			->method( 'next' )
			->will( $this->onConsecutiveCalls( $searchresult, false ) );

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
			->with( $this->equalTo( $title ) )
			->will( $this->returnValue( true ) );

		$interlanguageLinksLookup->expects( $this->atLeastOnce() )
			->method( 'findPageLanguageForTarget' )
			->with( $this->equalTo( $title ) )
			->will( $this->returnValue( 'mhr' ) );

		$instance = new LanguageResultMatchFinder( $interlanguageLinksLookup );

		$searchresult = $this->getMockBuilder( '\SearchResult' )
			->disableOriginalConstructor()
			->getMock();

		$searchresult->expects( $this->atLeastOnce() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$searchResultSet = $this->getMockBuilder( '\SearchResultSet' )
			->disableOriginalConstructor()
			->getMock();

		$searchResultSet->expects( $this->any() )
			->method( 'next' )
			->will( $this->onConsecutiveCalls( $searchresult, $searchresult, false ) );

		$this->assertInstanceOf(
			'\SIL\Search\MappedSearchResultSet',
			$instance->matchResultsToLanguage( $searchResultSet, 'mhr' )
		);
	}

}
