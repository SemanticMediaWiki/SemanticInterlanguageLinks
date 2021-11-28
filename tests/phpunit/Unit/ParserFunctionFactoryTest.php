<?php

namespace SIL\Tests;

use MediaWiki\MediaWikiServices;
use SIL\ParserFunctionFactory;
use Title;
use ParserOptions;

/**
 * @covers \SIL\ParserFunctionFactory
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ParserFunctionFactoryTest extends \PHPUnit_Framework_TestCase {

	private $parser;

	protected function setUp(): void {
		parent::setUp();

		$factory = MediaWikiServices::getInstance()->getService( 'ParserFactory' );
		$this->parser = $factory->create();
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SIL\ParserFunctionFactory',
			new ParserFunctionFactory()
		);
	}

	public function testNewInterlanguageLinkParserFunctionDefinition() {

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$pageContentLanguageOnTheFlyModifier = $this->getMockBuilder( '\SIL\PageContentLanguageOnTheFlyModifier' )
			->disableOriginalConstructor()
			->getMock();

		$this->parser->setTitle( Title::newFromText( __METHOD__ ) );

		$instance = new ParserFunctionFactory();

		list( $name, $definition, $flag ) = $instance->newInterlanguageLinkParserFunctionDefinition(
			$interlanguageLinksLookup,
			$pageContentLanguageOnTheFlyModifier
		);

		$this->assertEquals(
			'interlanguagelink',
			$name
		);

		$this->assertInstanceOf(
			'\Closure',
			$definition
		);

		$text = '';

		$this->assertNotEmpty(
			call_user_func_array( $definition, [ $this->parser, $text ] )
		);
	}

	public function testNewInterlanguageListParserFunctionDefinition() {

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$this->parser->setTitle( Title::newFromText( __METHOD__ ) );

		$instance = new ParserFunctionFactory();

		list( $name, $definition, $flag ) = $instance->newInterlanguageListParserFunctionDefinition(
			$interlanguageLinksLookup
		);

		$this->assertEquals(
			'interlanguagelist',
			$name
		);

		$this->assertInstanceOf(
			'\Closure',
			$definition
		);

		$text = '';

		$this->assertNotEmpty(
			call_user_func_array( $definition, [ $this->parser, $text ] )
		);
	}

	public function testNewAnnotatedLanguageParserFunctionDefinition() {

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$this->parser->setTitle( Title::newFromText( __METHOD__ ) );

		$instance = new ParserFunctionFactory();

		list( $name, $definition, $flag ) = $instance->newAnnotatedLanguageParserFunctionDefinition(
			$interlanguageLinksLookup
		);

		$this->assertEquals(
			'annotatedlanguage',
			$name
		);

		$text = '';

		$this->assertEmpty(
			call_user_func_array( $definition, [ $this->parser, $text ] )
		);
	}

}
