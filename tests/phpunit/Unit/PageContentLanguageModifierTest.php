<?php

namespace SIL\Tests;

use SIL\PageContentLanguageModifier;

/**
 * @covers \SIL\PageContentLanguageModifier
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

		$cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\PageContentLanguageModifier',
			new PageContentLanguageModifier( $interlanguageLinksLookup, $cache )
		);
	}

	public function testGetValidPageContentLanguage() {

		$pageLanguage = '';

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'findPageLanguageForTarget' )
			->will( $this->returnValue(  'ja' ) );

		$instance = new PageContentLanguageModifier(
			$interlanguageLinksLookup,
			$cache
		);

		$this->assertEquals(
			'ja',
			$instance->getPageContentLanguage( $title, $pageLanguage )
		);
	}

	public function testGetValidPageContentLanguageFromCache() {

		$pageLanguage = '';

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMock();

		$cache->expects( $this->once() )
			->method( 'fetch' )
			->will( $this->returnValue(  'fr' ) );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->never() )
			->method( 'findPageLanguageForTarget' );

		$instance = new PageContentLanguageModifier(
			$interlanguageLinksLookup,
			$cache
		);

		$this->assertEquals(
			'fr',
			$instance->getPageContentLanguage( $title, $pageLanguage )
		);
	}

	public function testGetPageContentLanguageToReturnLanguageCode() {

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$pageLanguage = $this->getMockBuilder( '\Language' )
			->disableOriginalConstructor()
			->getMock();

		$pageLanguage->expects( $this->once() )
			->method( 'getCode' )
			->will( $this->returnValue( 'en' ) );

		$instance = new PageContentLanguageModifier(
			$interlanguageLinksLookup,
			$cache
		);

		$this->assertEquals(
			'en',
			$instance->getPageContentLanguage( $title, $pageLanguage )
		);
	}

	/**
	 * @dataProvider invalidLanguageCodeProvider
	 */
	public function testPageContentLanguageOnInvalidLanguage( $invalidLanguageCode ) {

		$pageLanguage = '';

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'findPageLanguageForTarget' )
			->will( $this->returnValue( $invalidLanguageCode ) );

		$instance = new PageContentLanguageModifier(
			$interlanguageLinksLookup,
			$cache
		);

		$this->assertEmpty(
			$instance->getPageContentLanguage( $title, $pageLanguage )
		);
	}

	public function invalidLanguageCodeProvider() {

		$provider = array(
			array( null ),
			array( '' ),
			array( false )
		);

		return $provider;
	}

}
