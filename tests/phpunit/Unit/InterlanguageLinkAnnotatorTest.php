<?php

namespace SIL\Tests;

use SIL\InterlanguageLinkAnnotator;
use SIL\InterlanguageLink;

/**
 * @covers \SIL\InterlanguageLinkAnnotator
 *
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class InterlanguageLinkAnnotatorTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$parserData = $this->getMockBuilder( '\SMW\ParserData' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\InterlanguageLinkAnnotator',
			new InterlanguageLinkAnnotator( $parserData )
		);
	}

	public function testAddAnnotation() {

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

		$instance = new InterlanguageLinkAnnotator( $parserData );

		$instance->addAnnotationFor(
			new InterlanguageLink( 'en', 'bar' )
		);
	}

}
