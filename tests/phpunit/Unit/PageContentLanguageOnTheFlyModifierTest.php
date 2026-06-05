<?php

namespace SIL\Tests;

use MediaWiki\Title\Title;
use SIL\PageContentLanguageOnTheFlyModifier;
use Wikimedia\ObjectCache\BagOStuff;

/**
 * @covers \SIL\PageContentLanguageOnTheFlyModifier
 * @group semantic-interlanguage-links
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class PageContentLanguageOnTheFlyModifierTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$cache = $this->getMockBuilder( BagOStuff::class )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\PageContentLanguageOnTheFlyModifier',
			new PageContentLanguageOnTheFlyModifier( $interlanguageLinksLookup, $cache )
		);
	}

	public function testGetValidPageContentLanguage() {
		$pageLanguage = '';

		$title = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$cache = $this->getMockBuilder( BagOStuff::class )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'findPageLanguageForTarget' )
			->willReturn( 'zh-Hans' );

		$instance = new PageContentLanguageOnTheFlyModifier(
			$interlanguageLinksLookup,
			$cache
		);

		$this->assertEquals(
			'zh-hans',
			$instance->getPageContentLanguage( $title, $pageLanguage )
		);
	}

	public function testGetValidPageContentLanguageFromCache() {
		$pageLanguage = '';

		$title = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$cache = $this->getMockBuilder( BagOStuff::class )
			->disableOriginalConstructor()
			->getMock();

		$cache->expects( $this->once() )
			->method( 'get' )
			->willReturn( 'zh-Hans' );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->never() )
			->method( 'findPageLanguageForTarget' );

		$instance = new PageContentLanguageOnTheFlyModifier(
			$interlanguageLinksLookup,
			$cache
		);

		$this->assertEquals(
			'zh-hans',
			$instance->getPageContentLanguage( $title, $pageLanguage )
		);
	}

	public function testGetPageContentLanguageToReturnLanguageCode() {
		$title = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$cache = $this->getMockBuilder( BagOStuff::class )
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
			->willReturn( 'en' );

		$instance = new PageContentLanguageOnTheFlyModifier(
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

		$title = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$cache = $this->getMockBuilder( BagOStuff::class )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'findPageLanguageForTarget' )
			->willReturn( $invalidLanguageCode );

		$instance = new PageContentLanguageOnTheFlyModifier(
			$interlanguageLinksLookup,
			$cache
		);

		$this->assertEmpty(
			$instance->getPageContentLanguage( $title, $pageLanguage )
		);
	}

	public function testAddToIntermediaryCache() {
		$title = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'getPrefixedText' )
			->willReturn( 'Foo' );

		$cache = $this->getMockBuilder( BagOStuff::class )
			->disableOriginalConstructor()
			->getMock();

		$cache->expects( $this->once() )
			->method( 'set' )
			->with(
				$this->anything(),
				'BAR' );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new PageContentLanguageOnTheFlyModifier(
			$interlanguageLinksLookup,
			$cache
		);

		$instance->addToIntermediaryCache( $title, 'BAR' );
	}

	public function invalidLanguageCodeProvider() {
		$provider = [
			[ null ],
			[ '' ],
			[ false ]
		];

		return $provider;
	}

}
