<?php

namespace SIL\Tests;

use SIL\AnnotatedLanguageParserFunction;

/**
 * @covers \SIL\AnnotatedLanguageParserFunction
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class AnnotatedLanguageParserFunctionTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			AnnotatedLanguageParserFunction::class,
			new AnnotatedLanguageParserFunction( $interlanguageLinksLookup )
		);
	}

	public function testParseOnMissingAnnotatedLanguage() {

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'findPageLanguageForTarget' )
			->will( $this->returnValue( '' ) );

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'getRedirectTargetFor' )
			->will( $this->returnValue( \Title::newFromText( 'Foo' ) ) );

		$instance = new AnnotatedLanguageParserFunction(
			$interlanguageLinksLookup
		);

		$this->assertEquals(
			'',
			$instance->parse( \Title::newFromText( 'Foo' ), 'FakeTemplate' )
		);
	}

	public function testParseOnAnnotatedLanguageWithoutTemplate() {

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'findPageLanguageForTarget' )
			->will( $this->returnValue( 'en' ) );

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'getRedirectTargetFor' )
			->will( $this->returnValue( \Title::newFromText( 'Foo' ) ) );

		$instance = new AnnotatedLanguageParserFunction(
			$interlanguageLinksLookup
		);

		$expected = 'en';

		$this->assertEquals(
			$expected,
			$instance->parse( \Title::newFromText( 'Foo' ), '' )
		);
	}

	public function testParseOnAnnotatedLanguageWithTemplate() {

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'findPageLanguageForTarget' )
			->will( $this->returnValue( 'en' ) );

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'getRedirectTargetFor' )
			->will( $this->returnValue( \Title::newFromText( 'Foo' ) ) );

		$instance = new AnnotatedLanguageParserFunction(
			$interlanguageLinksLookup
		);

		$expected = '{{FakeTemplate|target-link=Foo|lang-code=en|lang-name=English}}';

		$this->assertEquals(
			[ $expected, "noparse" => false, "isHTML" => false ],
			$instance->parse( \Title::newFromText( 'Foo' ), 'FakeTemplate' )
		);
	}

}
