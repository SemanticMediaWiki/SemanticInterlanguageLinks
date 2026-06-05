<?php

namespace SIL\Tests\Hooks;

use MediaWiki\Parser\Parser;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Title\Title;
use SIL\Hooks\ParserHooks;
use SIL\InterlanguageLinksLookup;
use SIL\PageContentLanguageOnTheFlyModifier;

/**
 * @covers \SIL\Hooks\ParserHooks
 * @group semantic-interlanguage-links
 *
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */
class ParserHooksTest extends \PHPUnit\Framework\TestCase {

	private $interlanguageLinksLookup;
	private $pageContentLanguageOnTheFlyModifier;

	protected function setUp(): void {
		parent::setUp();

		$this->interlanguageLinksLookup = $this->getMockBuilder( InterlanguageLinksLookup::class )
			->disableOriginalConstructor()
			->getMock();

		$this->pageContentLanguageOnTheFlyModifier = $this->getMockBuilder( PageContentLanguageOnTheFlyModifier::class )
			->disableOriginalConstructor()
			->getMock();
	}

	private function newInstance(): ParserHooks {
		return new ParserHooks(
			$this->interlanguageLinksLookup,
			$this->pageContentLanguageOnTheFlyModifier
		);
	}

	public function testCanConstruct(): void {
		$this->assertInstanceOf(
			ParserHooks::class,
			$this->newInstance()
		);
	}

	public function testOnParserFirstCallInit(): void {
		$parser = $this->getMockBuilder( Parser::class )
			->disableOriginalConstructor()
			->getMock();

		$parser->expects( $this->exactly( 3 ) )
			->method( 'setFunctionHook' );

		$this->assertTrue(
			$this->newInstance()->onParserFirstCallInit( $parser )
		);
	}

	public function testOnParserAfterTidy(): void {
		$title = Title::newFromText( __METHOD__ );

		$parserOutput = $this->getMockBuilder( ParserOutput::class )
			->disableOriginalConstructor()
			->getMock();

		$parser = $this->getMockBuilder( Parser::class )
			->disableOriginalConstructor()
			->getMock();

		$parser->expects( $this->any() )
			->method( 'getTitle' )
			->willReturn( $title );

		$parser->expects( $this->any() )
			->method( 'getOutput' )
			->willReturn( $parserOutput );

		$text = '';

		$this->assertTrue(
			$this->newInstance()->onParserAfterTidy( $parser, $text )
		);
	}

}
