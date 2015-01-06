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

	public function testTryParseThatCausesErrorMessage() {

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

		$this->assertContains(
			'span class="error"',
			$instance->parse( '{[[:Template:Foo]]', 'en' )
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

	/**
	 * @dataProvider languageTargetLinksTemplateProvider
	 */
	public function testParseForValidLanguageTargetLinks( $targetLink, $expected ) {

		$parser = new \Parser();
		$parser->setTitle( \Title::newFromText( __METHOD__ ) );
		$parser->Options( new \ParserOptions() );
		$parser->clearState();

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'queryLanguageTargetLinks' )
			->will( $this->returnValue( $targetLink ) );

		$instance = new InterlanguageListParserFunction(
			$parser,
			$interlanguageLinksLookup
		);

		$text = $instance->parse( 'Foo', 'FakeTemplate' );

		$this->assertInternalType(
			'array',
			$text
		);

		$this->assertContains(
			$expected,
			$text[0]
		);
	}

	public function languageTargetLinksTemplateProvider() {

		$provider = array();

		$provider[] = array(
			array( 'en' => 'test' ),
			'{{FakeTemplate' .
			'|list-pos=0' .
			'|target-link=Test' .
			'|lang-code=en' .
			'|lang-name=English}}'
		);

		$provider[] = array(
			array( 'ja' => \Title::newFromText( 'テスト' ) ),
			'{{FakeTemplate' .
			'|list-pos=0' .
			'|target-link=テスト' .
			'|lang-code=ja' .
			'|lang-name=日本語}}'
		);

		$provider[] = array(
			array( 'zh-hans' => \Title::newFromText( '分类：汉字' ) ),
			'{{FakeTemplate' .
			'|list-pos=0' .
			'|target-link=分类：汉字' .
			'|lang-code=zh-Hans' .
			'|lang-name=中文（简体）‎}}'
		);

		$provider[] = array(
			array( 'zh-hans' => \Title::newFromText( 'Category:汉字' ) ),
			'{{FakeTemplate' .
			'|list-pos=0' .
			'|target-link=:Category:汉字' .
			'|lang-code=zh-Hans' .
			'|lang-name=中文（简体）‎}}'
		);

		$provider[] = array(
			array( 'de' => 'Category:Foo' ),
			'{{FakeTemplate' .
			'|list-pos=0' .
			'|target-link=:Category:Foo' .
			'|lang-code=de' .
			'|lang-name=Deutsch}}'
		);

		return $provider;
	}

}
