<?php

namespace SIL\Tests;

use SIL\SiteLanguageLinksParserOutputAppender;
use SIL\InterlanguageLink;

use SMW\DIWikiPage;
use SMWDIBlob as DIBlob;

/**
 * @covers \SIL\SiteLanguageLinksParserOutputAppender
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class SiteLanguageLinksParserOutputAppenderTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$parserOutput = $this->getMockBuilder( '\ParserOutput' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\SiteLanguageLinksParserOutputAppender',
			new SiteLanguageLinksParserOutputAppender(
				$parserOutput,
				$interlanguageLinksLookup
			)
		);
	}

	public function testAddLanguageTargetLinksToOutput() {

		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );

		$parserOutput = $this->getMockBuilder( '\ParserOutput' )
			->disableOriginalConstructor()
			->getMock();

		$parserOutput->expects( $this->once() )
			->method( 'addLanguageLink' );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'queryLanguageTargetLinks' )
			->with( $this->equalTo( $interlanguageLink ) )
			->will( $this->returnValue( [ 'fr' => 'Bar' ] ) );

		$instance = new SiteLanguageLinksParserOutputAppender(
			$parserOutput,
			$interlanguageLinksLookup
		);

		$instance->tryAddLanguageTargetLinksToOutput( $interlanguageLink );
	}

	public function testCompareLanguageTargetLinksForExistingLanguageEntry() {

		$interlanguageLink = new InterlanguageLink( 'en', 'Yui' );

		$parserOutput = $this->getMockBuilder( '\ParserOutput' )
			->disableOriginalConstructor()
			->getMock();

		$parserOutput->expects( $this->never() )
			->method( 'addLanguageLink' );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'queryLanguageTargetLinks' )
			->with( $this->equalTo( $interlanguageLink ) )
			->will( $this->returnValue( [ 'en' => \Title::newFromText( 'Foo' ) ] ) );

		$instance = new SiteLanguageLinksParserOutputAppender(
			$parserOutput,
			$interlanguageLinksLookup
		);

		$knownTarget = $instance->tryAddLanguageTargetLinksToOutput(
			$interlanguageLink
		);

		$this->assertFalse(
			 $knownTarget
		);
	}

	public function testAddLanguageTargetLinksToOutputFromStoreForMultipleInvocation() {

		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );

		$parserOutput = $this->getMockBuilder( '\ParserOutput' )
			->disableOriginalConstructor()
			->getMock();

		$parserOutput->expects( $this->once() )
			->method( 'addLanguageLink' )
			->with( $this->equalTo( 'sil:vi:Yan' ) );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->atLEastOnce() )
			->method( 'queryLanguageTargetLinks' )
			->with( $this->equalTo( $interlanguageLink ) )
			->will( $this->returnValue( [ 'vi' => \Title::newFromText( 'Yan' ) ] ) );

		$instance = new SiteLanguageLinksParserOutputAppender(
			$parserOutput,
			$interlanguageLinksLookup
		);

		$instance->tryAddLanguageTargetLinksToOutput( $interlanguageLink );

		// Simualate call from a second parser call
		$instance->tryAddLanguageTargetLinksToOutput( $interlanguageLink );
	}

}
