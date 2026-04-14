<?php

namespace SIL\Tests;

use MediaWiki\Interwiki\ClassicInterwikiLookup;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Parser\ParserOutputLinkTypes;
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

	private $interwikiCache = null;

	protected function setUp(): void {
		global $wgInterwikiCache;

		parent::setUp();

		// We don't have MediaWikiIntegrationTestCase's methods available, so we have to do it ourself.
		$this->interwikiCache = $wgInterwikiCache;
		$wgInterwikiCache = ClassicInterwikiLookup::buildCdbHash( [
			[
				'iw_prefix' => 'en',
				'iw_url' => '//en.wikipedia.org/wiki/$1',
				'iw_local' => 1
			],
		] );
		// UserFactory holds UserNameUtils holds
		// TitleParser (aka _MediaWikiTitleCodec) holds InterwikiLookup
		MediaWikiServices::getInstance()->resetServiceForTesting( 'InterwikiLookup' );
		MediaWikiServices::getInstance()->resetServiceForTesting( '_MediaWikiTitleCodec' );
		MediaWikiServices::getInstance()->resetServiceForTesting( 'TitleParser' );
		MediaWikiServices::getInstance()->resetServiceForTesting( 'UserNameUtils' );
		MediaWikiServices::getInstance()->resetServiceForTesting( 'UserFactory' );
	}

	protected function tearDown(): void {
		global $wgInterwikiCache;

		$wgInterwikiCache = $this->interwikiCache;
		MediaWikiServices::getInstance()->resetServiceForTesting( 'InterwikiLookup' );
		MediaWikiServices::getInstance()->resetServiceForTesting( '_MediaWikiTitleCodec' );
		MediaWikiServices::getInstance()->resetServiceForTesting( 'TitleParser' );
		MediaWikiServices::getInstance()->resetServiceForTesting( 'UserNameUtils' );
		MediaWikiServices::getInstance()->resetServiceForTesting( 'UserFactory' );

		parent::tearDown();
	}

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
		$parserOutput->addLanguageLink( 'sil:en:Foo' );

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
		$parserOutput->addLanguageLink( 'invalid:Foo' );

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

		$parserOutput = new ParserOutput();
		$parserOutput->addLanguageLink( $title );

		$instance = new InterwikiLanguageLinkFetcher( $languageLinkAnnotator );
		$instance->fetchLanguagelinksFromParserOutput( $parserOutput );
	}

}
