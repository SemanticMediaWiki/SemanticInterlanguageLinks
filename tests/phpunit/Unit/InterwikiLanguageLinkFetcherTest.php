<?php

namespace SIL\Tests;

use MediaWiki\Interwiki\ClassicInterwikiLookup;
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
		$GLOBALS['wgInterwikiCache'] = ClassicInterwikiLookup::buildCdbHash( [
			[
				'iw_prefix' => 'iw-test',
				'iw_url' => 'http://www.example.org/$1',
				'iw_api' => '',
				'iw_wikiid' => 'foo',
				'iw_local' => 1,
			],
		] );
		\MediaWiki\MediaWikiServices::getInstance()->resetServiceForTesting( 'InterwikiLookup' );

		$languageLinkAnnotator = $this->getMockBuilder( '\SIL\LanguageLinkAnnotator' )
			->disableOriginalConstructor()
			->getMock();

		$languageLinkAnnotator->expects( $this->never() )
			->method( 'addAnnotationForInterwikiLanguageLink' );

		$parserOutput = new ParserOutput();
		$parserOutput->addLanguageLink( 'invalid:Foo' );

		print_r( 'test1' );
		print_r( $parserOutput->getLinkList( ParserOutputLinkTypes::LANGUAGE ) );
		// print_r( Title::castFromLinkTarget( $parserOutput->getLinkList( ParserOutputLinkTypes::LANGUAGE )[0]['link'] )->isValid() ? 'works' : 'failed' );
		$test = Title::castFromLinkTarget( $parserOutput->getLinkList( ParserOutputLinkTypes::LANGUAGE )[0]['link'] );
		print_r( \MediaWiki\MediaWikiServices::getInstance()->getInterwikiLookup()->isValidInterwiki( $test->getInterwiki() ) ? 'worked' : 'failed' );

		$instance = new InterwikiLanguageLinkFetcher( $languageLinkAnnotator );
		$instance->fetchLanguagelinksFromParserOutput( $parserOutput );
	}

	public function testValidInterwikiLink() {
		$GLOBALS['wgInterwikiCache'] = ClassicInterwikiLookup::buildCdbHash( [
			[
				'iw_prefix' => 'iw-test',
				'iw_url' => 'http://www.example.org/$1',
				'iw_api' => '',
				'iw_wikiid' => 'foo',
				'iw_local' => 1,
			],
		] );
		\MediaWiki\MediaWikiServices::getInstance()->resetServiceForTesting( 'InterwikiLookup' );

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

		// print_r( $parserOutput->getLinkList( ParserOutputLinkTypes::LANGUAGE ) );
		print_r( 'test2' );
		// print_r( Title::castFromLinkTarget( $parserOutput->getLinkList( ParserOutputLinkTypes::LANGUAGE )[0]['link'] )->isValid() ? 'works' : 'failed' );
		$test = Title::castFromLinkTarget( $parserOutput->getLinkList( ParserOutputLinkTypes::LANGUAGE )[0]['link'] );
		print_r( \MediaWiki\MediaWikiServices::getInstance()->getInterwikiLookup()->isValidInterwiki( $test->getInterwiki() ) ? 'worked' : 'failed' );

		$instance = new InterwikiLanguageLinkFetcher( $languageLinkAnnotator );
		$instance->fetchLanguagelinksFromParserOutput( $parserOutput );
	}

}
