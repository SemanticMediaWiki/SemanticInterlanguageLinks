<?php

namespace SIL\Tests;

use SIL\InterlanguageLink;
use SIL\InterwikiLanguageLink;
use SIL\LanguageLinkAnnotator;
use SIL\PropertyRegistry;
use SMW\DIWikiPage;

/**
 * @covers \SIL\LanguageLinkAnnotator
 * @group semantic-interlanguage-links
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class LanguageLinkAnnotatorTest extends \PHPUnit\Framework\TestCase {

	protected function setUp(): void {
		parent::setUp();

		$propertyRegistry = $this->getMockBuilder( '\SMW\PropertyRegistry' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new PropertyRegistry();
		$instance->register( $propertyRegistry );
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

	/**
	 * @dataProvider differentLanguageAnnotationProvider
	 */
	public function testHasDifferentLanguageAnnotation( $pValues, $expected ) {
		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->willReturn( $pValues );

		$parserData = $this->getMockBuilder( '\SMW\ParserData' )
			->disableOriginalConstructor()
			->getMock();

		$parserData->expects( $this->once() )
			->method( 'getSemanticData' )
			->willReturn( $semanticData );

		$instance = new LanguageLinkAnnotator( $parserData );

		$result = $instance->hasDifferentLanguageAnnotation(
			new InterlanguageLink( 'ja', 'bar' )
		);

		$this->assertEquals(
			$expected,
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
			->willReturn( \Title::newFromText( 'Foo' ) );

		$parserData->expects( $this->once() )
			->method( 'getSemanticData' )
			->willReturn( $semanticData );

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
			->willReturn( \Title::newFromText( 'Foo' ) );

		$parserData->expects( $this->once() )
			->method( 'getSemanticData' )
			->willReturn( $semanticData );

		$parserData->expects( $this->once() )
			->method( 'pushSemanticDataToParserOutput' );

		$parserData->expects( $this->once() )
			->method( 'setSemanticDataStateToParserOutputProperty' );

		$instance = new LanguageLinkAnnotator( $parserData );

		$instance->addAnnotationForInterwikiLanguageLink(
			new InterwikiLanguageLink( 'en:Foo' )
		);
	}

	public function testCanAddAnnotation() {
		$parserData = $this->getMockBuilder( '\SMW\ParserData' )
			->disableOriginalConstructor()
			->getMock();

		// SMW 3.0
		$parserData->expects( $this->any() )
			->method( 'canUse' )
			->willReturn( false );

		$instance = new LanguageLinkAnnotator( $parserData );

		$this->assertFalse(
			$instance->canAddAnnotation()
		);
	}

	public function differentLanguageAnnotationProvider() {
		$provider[] = [
			[ new DIWikiPage( 'Foo', NS_MAIN, '', 'ill.en' ) ],
			true
		];

		$provider[] = [
			[],
			false
		];

		return $provider;
	}

}
