<?php

namespace SIL\Tests;

use SIL\LanguageLinkAnnotator;
use SIL\InterlanguageLink;
use SIL\InterwikiLanguageLink;
use SIL\PropertyRegistry;
use SMW\DIWikiPage;

/**
 * @covers \SIL\LanguageLinkAnnotator
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class LanguageLinkAnnotatorTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
		parent::setUp();

		$propertyRegistry = new PropertyRegistry();
		$propertyRegistry->register();
	}

	public function testCanConstruct() {

		$parserData = $this->getMockBuilder( '\SMW\ParserData' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\LanguageLinkAnnotator',
			new LanguageLinkAnnotator( $parserData )
		);
	}

	public function testHasDifferentLanguageAnnotation() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->will( $this->returnValue( array( new DIWikiPage( 'Foo', NS_MAIN, '' , 'ill.en' ) ) ) );

		$parserData = $this->getMockBuilder( '\SMW\ParserData' )
			->disableOriginalConstructor()
			->getMock();

		$parserData->expects( $this->once() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$instance = new LanguageLinkAnnotator( $parserData );

		$result = $instance->hasDifferentLanguageAnnotation(
			new InterlanguageLink( 'ja', 'bar' )
		);

		$this->assertTrue(
			$result
		);
	}

	public function testAddAnnotationForInterlanguageLink() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$parserData = $this->getMockBuilder( '\SMW\ParserData' )
			->disableOriginalConstructor()
			->getMock();

		$parserData->expects( $this->once() )
			->method( 'getTitle' )
			->will( $this->returnValue( \Title::newFromText( 'Foo' ) ) );

		$parserData->expects( $this->once() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$parserData->expects( $this->once() )
			->method( 'pushSemanticDataToParserOutput' );

		$parserData->expects( $this->once() )
			->method( 'setSemanticDataStateToParserOutputProperty' );

		$instance = new LanguageLinkAnnotator( $parserData );

		$instance->addAnnotationForInterlanguageLink(
			new InterlanguageLink( 'en', 'bar' )
		);
	}

	public function testAddAnnotationForInterwikiLanguageLink() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$parserData = $this->getMockBuilder( '\SMW\ParserData' )
			->disableOriginalConstructor()
			->getMock();

		$parserData->expects( $this->once() )
			->method( 'getTitle' )
			->will( $this->returnValue( \Title::newFromText( 'Foo' ) ) );

		$parserData->expects( $this->once() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$parserData->expects( $this->once() )
			->method( 'pushSemanticDataToParserOutput' );

		$parserData->expects( $this->once() )
			->method( 'setSemanticDataStateToParserOutputProperty' );

		$instance = new LanguageLinkAnnotator( $parserData );

		$instance->addAnnotationForInterwikiLanguageLink(
			new InterwikiLanguageLink( 'en:Foo' )
		);
	}

}
