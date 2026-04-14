<?php

namespace SIL\Tests;

use MediaWiki\Interwiki\InterwikiLookup;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Title\Title;
use SIL\InterwikiLanguageLinkFetcher;

/**
 * @covers \SIL\InterwikiLanguageLinkFetcher
 *
 * @group semantic-interlanguage-links
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class InterwikiLanguageLinkFetcherTest extends \PHPUnit\Framework\TestCase {

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

		$parserOutput = new ParserOutput();
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

		$parserOutput = new ParserOutput();
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

		$parserOutput = new ParserOutput();
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

		$title = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->any() )
			->method( 'getInterwiki' )
			->willReturn( 'en' );

		MediaWikiServices::getInstance()->resetServiceForTesting( 'InterwikiLookup' );

		$services = MediaWikiServices::getInstance();

		$mockLookup = $this->createMock( InterwikiLookup::class );
		$mockLookup->method( 'isValidInterwiki' )
			->willReturnCallback( static fn ( $key ) => $key === 'en' );

		$services->redefineService(
			'InterwikiLookup',
			static fn () => $mockLookup
		);

		$parserOutput = new ParserOutput();
		$parserOutput->setLanguageLinks( [ $title ] );

		print_r( $parserOutput->getLanguageLinks() );
		print_r( Title::newFromText( 'en:en' ) );

		$instance = new InterwikiLanguageLinkFetcher( $languageLinkAnnotator );
		$instance->fetchLanguagelinksFromParserOutput( $parserOutput );
	}

}
