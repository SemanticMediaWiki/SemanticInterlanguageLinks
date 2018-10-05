<?php

namespace SIL\Tests;

use SIL\InterwikiLanguageLinkFetcher;
use SIL\PropertyRegistry;

use Title;

/**
 * @covers \SIL\InterwikiLanguageLinkFetcher
 *
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class InterwikiLanguageLinkFetcherTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$languageLinkAnnotator = $this->getMockBuilder( '\SIL\LanguageLinkAnnotator' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\InterwikiLanguageLinkFetcher',
			new InterwikiLanguageLinkFetcher( $languageLinkAnnotator )
		);
	}

	public function testEmptyLanguageLinks() {

		$languageLinkAnnotator = $this->getMockBuilder( '\SIL\LanguageLinkAnnotator' )
			->disableOriginalConstructor()
			->getMock();

		$languageLinkAnnotator->expects( $this->never() )
			->method( 'addAnnotationForInterwikiLanguageLink' );

		$parserOutput = new \ParserOutput();
		$parserOutput->setLanguageLinks( [] );

		$instance = new InterwikiLanguageLinkFetcher( $languageLinkAnnotator );
		$instance->fetchLanguagelinksFromParserOutput( $parserOutput );
	}

	public function testIgnoreSILLink() {

		$languageLinkAnnotator = $this->getMockBuilder( '\SIL\LanguageLinkAnnotator' )
			->disableOriginalConstructor()
			->getMock();

		$languageLinkAnnotator->expects( $this->never() )
			->method( 'addAnnotationForInterwikiLanguageLink' );

		$parserOutput = new \ParserOutput();
		$parserOutput->setLanguageLinks( [ 'sil:en:Foo' ] );

		$instance = new InterwikiLanguageLinkFetcher( $languageLinkAnnotator );
		$instance->fetchLanguagelinksFromParserOutput( $parserOutput );
	}

	public function testInvalidInterwikiLink() {

		$languageLinkAnnotator = $this->getMockBuilder( '\SIL\LanguageLinkAnnotator' )
			->disableOriginalConstructor()
			->getMock();

		$languageLinkAnnotator->expects( $this->never() )
			->method( 'addAnnotationForInterwikiLanguageLink' );

		$parserOutput = new \ParserOutput();
		$parserOutput->setLanguageLinks( [ 'invalid:Foo' ] );

		$instance = new InterwikiLanguageLinkFetcher( $languageLinkAnnotator );
		$instance->fetchLanguagelinksFromParserOutput( $parserOutput );
	}

	public function testValidInterwikiLink() {

		$parserData = $this->getMockBuilder( '\SMW\ParserData' )
			->disableOriginalConstructor()
			->getMock();

		$languageLinkAnnotator = $this->getMockBuilder( '\SIL\LanguageLinkAnnotator' )
			->setConstructorArgs( [ $parserData ] )
			->getMock();

		$languageLinkAnnotator->expects( $this->once() )
			->method( 'addAnnotationForInterwikiLanguageLink' );

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->any() )
			->method( 'getInterwiki' )
			->will( $this->returnValue( 'en' ) );

		$parserOutput = new \ParserOutput();
		$parserOutput->setLanguageLinks( [ $title ] );

		$instance = new InterwikiLanguageLinkFetcher( $languageLinkAnnotator );
		$instance->fetchLanguagelinksFromParserOutput( $parserOutput );
	}

}
