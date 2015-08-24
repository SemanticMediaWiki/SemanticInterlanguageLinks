<?php

namespace SIL\Tests;

use SIL\SiteLanguageLinksGenerator;
use SIL\InterlanguageLink;

use SMW\DIWikiPage;
use SMWDIBlob as DIBlob;

/**
 * @covers \SIL\SiteLanguageLinksGenerator
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class SiteLanguageLinksGeneratorTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$parserOutput = $this->getMockBuilder( '\ParserOutput' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\SiteLanguageLinksGenerator',
			new SiteLanguageLinksGenerator(
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
			->will( $this->returnValue( array( 'fr' => 'Bar' ) ) );

		$instance = new SiteLanguageLinksGenerator(
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
			->will( $this->returnValue( array( 'en' => \Title::newFromText( 'Foo' ) ) ) );

		$instance = new SiteLanguageLinksGenerator(
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
			->will( $this->returnValue( array( 'vi' => \Title::newFromText( 'Yan' ) ) ) );

		$instance = new SiteLanguageLinksGenerator(
			$parserOutput,
			$interlanguageLinksLookup
		);

		$instance->tryAddLanguageTargetLinksToOutput( $interlanguageLink );

		// Simualate call from a second parser call
		$instance->tryAddLanguageTargetLinksToOutput( $interlanguageLink );
	}

}
