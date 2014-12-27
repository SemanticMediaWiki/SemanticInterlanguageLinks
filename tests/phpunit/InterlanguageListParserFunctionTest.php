<?php

namespace SIL\Tests;

use SIL\InterlanguageListParserFunction;

/**
 * @covers \SIL\InterlanguageListParserFunction
 *
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class InterlanguageListParserFunctionTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$parser = $this->getMockBuilder( '\Parser' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\InterlanguageListParserFunction',
			new InterlanguageListParserFunction(
				$parser,
				$interlanguageLinksLookup
			)
		);
	}

	public function testParseToCreateErrorMessage() {

		$parser = $this->getMockBuilder( '\Parser' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new InterlanguageListParserFunction(
			$parser,
			$interlanguageLinksLookup
		);

		$this->assertContains(
			'span class="error"',
			$instance->parse( '', 'Foo' )
		);

		$this->assertContains(
			'span class="error"',
			$instance->parse( 'Foo', '' )
		);
	}

	public function testParseForEmptyLanguageTargetLinks() {

		$parser = $this->getMockBuilder( '\Parser' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'getPageLanguageForTarget' )
			->with( $this->equalTo( \Title::newFromText( 'Foo' ) ) )
			->will( $this->returnValue( false ) );

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'tryCachedLanguageTargetLinks' )
			->will( $this->returnValue( array() ) );

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'queryLanguageTargetLinks' )
			->will( $this->returnValue( array() ) );

		$instance = new InterlanguageListParserFunction(
			$parser,
			$interlanguageLinksLookup
		);

		$this->assertEmpty(
			$instance->parse( 'Foo', 'FakeTemplate' )
		);
	}

	public function testParseForValidLanguageTargetLinks() {

		$parser = new \Parser();
		$parser->setTitle( \Title::newFromText( __METHOD__ ) );
		$parser->Options( new \ParserOptions() );
		$parser->clearState();

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'getPageLanguageForTarget' )
			->with( $this->equalTo( \Title::newFromText( 'Foo' ) ) )
			->will( $this->returnValue( false ) );

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'queryLanguageTargetLinks' )
			->will( $this->returnValue( array(
				'en' => 'test',
				'ja' => \Title::newFromText( 'テスト' ) ) ) );

		$instance = new InterlanguageListParserFunction(
			$parser,
			$interlanguageLinksLookup
		);

		$text = $instance->parse( 'Foo', 'FakeTemplate' );

		$expected = '{{FakeTemplate' .
			'|list-pos=0' .
			'|target-link=test' .
			'|lang-code=en' .
			'|lang-name=English' .
		'}}' . '{{FakeTemplate' .
			'|list-pos=1' .
			'|target-link=テスト' .
			'|lang-code=ja' .
			'|lang-name=日本語' .
		'}}';

		$this->assertInternalType(
			'array',
			$text
		);

		$this->assertContains(
			$expected,
			$text[0]
		);
	}

}
