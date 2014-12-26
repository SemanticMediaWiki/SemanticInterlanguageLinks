<?php

namespace SIL\Tests;

use SIL\ParserFunctionFactory;

use Title;
use Parser;
use ParserOptions;

/**
 * @covers \SIL\ParserFunctionFactory
 *
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ParserFunctionFactoryTest extends \PHPUnit_Framework_TestCase {

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

		$cachedSiteLanguageTargetLinks = $this->getMockBuilder( '\SIL\CachedSiteLanguageTargetLinks' )
			->disableOriginalConstructor()
			->getMock();

		$parser = new Parser();
		$parser->setTitle( Title::newFromText( __METHOD__ ) );
		$parser->Options( new ParserOptions() );
		$parser->clearState();

		$instance = new ParserFunctionFactory();

		list( $name, $definition, $flag ) = $instance->newInterlanguageLinkParserFunction(
			$interlanguageLinksLookup,
			$cachedSiteLanguageTargetLinks
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
			call_user_func_array( $definition, array( $parser, $text ) )
		);
	}

}
