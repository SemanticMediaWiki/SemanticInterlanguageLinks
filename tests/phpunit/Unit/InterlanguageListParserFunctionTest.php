<?php

namespace SIL\Tests;

use SIL\InterlanguageListParserFunction;
use SMW\Tests\PHPUnitCompat;

/**
 * @covers \SIL\InterlanguageListParserFunction
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class InterlanguageListParserFunctionTest extends \PHPUnit_Framework_TestCase {

	use PHPUnitCompat;

	public function testCanConstruct() {

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\InterlanguageListParserFunction',
			new InterlanguageListParserFunction(
				$interlanguageLinksLookup
			)
		);
	}

	public function testTryParseThatCausesErrorMessage() {

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new InterlanguageListParserFunction(
			$interlanguageLinksLookup
		);

		$this->assertContains(
			'div class="smw-callout smw-callout-error"',
			$instance->parse( '', 'Foo' )
		);

		$this->assertContains(
			'div class="smw-callout smw-callout-error"',
			$instance->parse( 'Foo', '' )
		);

		$this->assertContains(
			'div class="smw-callout smw-callout-error"',
			$instance->parse( '{[[:Template:Foo]]', 'en' )
		);
	}

	public function testParseForEmptyLanguageTargetLinks() {

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'queryLanguageTargetLinks' )
			->will( $this->returnValue( [] ) );

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'getRedirectTargetFor' )
			->will( $this->returnValue( 'Foo' ) );

		$instance = new InterlanguageListParserFunction(
			$interlanguageLinksLookup
		);

		$this->assertEquals(
			[ "", "noparse" => true, "isHTML" => false ],
			$instance->parse( 'Foo', 'FakeTemplate' )
		);
	}

	/**
	 * @dataProvider languageTargetLinksTemplateProvider
	 */
	public function testParseForValidLanguageTargetLinks( $targetLink, $expected ) {

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'queryLanguageTargetLinks' )
			->will( $this->returnValue( $targetLink ) );

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'getRedirectTargetFor' )
			->will( $this->returnValue( 'Foo' ) );

		$instance = new InterlanguageListParserFunction(
			$interlanguageLinksLookup
		);

		$this->assertEquals(
			[ $expected, "noparse" => false, "isHTML" => false ],
			$instance->parse( 'Foo', 'FakeTemplate' )
		);
	}

	public function languageTargetLinksTemplateProvider() {

		$provider = [];

		$provider[] = [
			[ 'en' => 'Test' ],
			'{{FakeTemplate' .
			'|#=0' .
			'|target-link=Test' .
			'|lang-code=en' .
			'|lang-name=English}}'
		];

		$provider[] = [
			[ 'ja' => \Title::newFromText( 'テスト' ) ],
			'{{FakeTemplate' .
			'|#=0' .
			'|target-link=テスト' .
			'|lang-code=ja' .
			'|lang-name=日本語}}'
		];

		$provider[] = [
			[ 'zh-hans' => \Title::newFromText( '分类：汉字' ) ],
			'{{FakeTemplate' .
			'|#=0' .
			'|target-link=分类：汉字' .
			'|lang-code=zh-Hans' .
			'|lang-name=中文（简体）‎}}'
		];

		$categoryNS = $GLOBALS['wgContLang']->getNsText( NS_CATEGORY );

		$provider[] = [
			[ 'zh-hans' => \Title::newFromText( 'Category:汉字' ) ],
			'{{FakeTemplate' .
			'|#=0' .
			"|target-link=:$categoryNS:汉字" .
			'|lang-code=zh-Hans' .
			'|lang-name=中文（简体）‎}}'
		];

		$provider[] = [
			[ 'de' => 'Category:Foo' ],
			'{{FakeTemplate' .
			'|#=0' .
			"|target-link=:$categoryNS:Foo" .
			'|lang-code=de' .
			'|lang-name=Deutsch}}'
		];

		return $provider;
	}

}
