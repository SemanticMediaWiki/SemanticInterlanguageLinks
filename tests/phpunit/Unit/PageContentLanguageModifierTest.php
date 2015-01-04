<?php

namespace SIL\Tests;

use SIL\PageContentLanguageModifier;
use SMWDIBlob as DIBlob;

/**
 * @covers \SIL\PageContentLanguageModifier
 *
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class PageContentLanguageModifierTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\PageContentLanguageModifier',
			new PageContentLanguageModifier( $interlanguageLinksLookup, $title )
		);
	}

	public function testModifyLanguageFromInMemoryCache() {

		$pageLanguage = '';

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->atLeastOnce() )
			->method( 'getPrefixedDBKey' )
			->will( $this->returnValue( __METHOD__ ) );

		$instance = new PageContentLanguageModifier( $interlanguageLinksLookup, $title );

		$instance->clear();
		$instance->addLanguageToInMemoryCache( $title, 'en' );

		$instance->modifyLanguage( $pageLanguage );

		$this->assertEquals(
			'en',
			$pageLanguage
		);

		$instance->clear();
		$instance->addLanguageToInMemoryCache( \Title::newFromText( 'Foo' ), 'en' );

		$pageLanguage = 'NotModified';

		$instance->modifyLanguage( $pageLanguage );

		$this->assertEquals(
			'NotModified',
			$pageLanguage
		);
	}

	public function testModifyLanguageFromMatchedLookup() {

		$pageLanguage = '';

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->atLeastOnce() )
			->method( 'getPrefixedDBKey' )
			->will( $this->returnValue( __METHOD__ ) );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'findLastPageLanguageForTarget' )
			->will( $this->returnValue(  'lo' ) );

		$instance = new PageContentLanguageModifier( $interlanguageLinksLookup, $title );

		$this->assertTrue(
			$instance->modifyLanguage( $pageLanguage )
		);

		$this->assertEquals(
			'lo',
			$pageLanguage
		);
	}

	public function testModifyLanguageForNonCacheEntry() {

		$pageLanguage = '';

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->atLeastOnce() )
			->method( 'getPrefixedDBKey' )
			->will( $this->returnValue( __METHOD__ ) );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'findLastPageLanguageForTarget' )
			->will( $this->returnValue(  'ja' ) );

		$instance = new PageContentLanguageModifier( $interlanguageLinksLookup, $title );

		$this->assertTrue(
			$instance->modifyLanguage( $pageLanguage )
		);

		$this->assertEquals(
			'ja',
			$pageLanguage
		);
	}

	public function testModifyLanguageForNovalidLanguage() {

		$pageLanguage = '';

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->atLeastOnce() )
			->method( 'getPrefixedDBKey' )
			->will( $this->returnValue( __METHOD__ ) );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'findLastPageLanguageForTarget' )
			->will( $this->returnValue(  '' ) );

		$instance = new PageContentLanguageModifier( $interlanguageLinksLookup, $title );

		$this->assertTrue(
			$instance->modifyLanguage( $pageLanguage )
		);

		$this->assertEmpty(
			$pageLanguage
		);
	}
}
