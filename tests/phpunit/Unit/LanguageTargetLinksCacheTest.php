<?php

namespace SIL\Tests;

use SIL\LanguageTargetLinksCache;
use SIL\InterlanguageLink;

use SMW\DIWikiPage;

use HashBagOStuff;
use Title;

/**
 * @covers \SIL\LanguageTargetLinksCache
 *
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class LanguageTargetLinksCacheTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$cache = $this->getMockBuilder( '\BagOStuff' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$this->assertInstanceOf(
			'\SIL\LanguageTargetLinksCache',
			new LanguageTargetLinksCache( $cache )
		);
	}

	public function testRoundtrip() {

		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );

		$languageTargetLinks = array(
			'bo' => 'Bar',
			'en' => Title::newFromText( 'Foo' )
		);

		$instance = new LanguageTargetLinksCache( new HashBagOStuff() );
		$instance->saveLanguageTargetLinksToCache( $interlanguageLink, $languageTargetLinks );

		$this->assertEquals(
			'bo',
			$instance->getPageLanguageFromCache( Title::newFromText( 'Bar' ) )
		);

		$this->assertEquals(
			$languageTargetLinks,
			$instance->getLanguageTargetLinksFromCache( $interlanguageLink )
		);

		$linkReferences = array();

		$instance->deleteLanguageTargetLinksFromCache( $linkReferences );
		$instance->deletePageLanguageForTargetFromCache( Title::newFromText( 'Bar' ) );

		$this->assertFalse(
			$instance->getPageLanguageFromCache( Title::newFromText( 'Bar' ) )
		);
	}

	public function testTryToGetLanguageTargetLinksForUnknownLanguageCode() {

		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );

		$languageTargetLinks = array(
			'bo' => 'Bar'
		);

		$instance = new LanguageTargetLinksCache( new HashBagOStuff() );
		$instance->saveLanguageTargetLinksToCache( $interlanguageLink, $languageTargetLinks );

		$this->assertFalse(
			$instance->getLanguageTargetLinksFromCache( $interlanguageLink )
		);
	}

	public function testTryToGetLanguageTargetLinksForNullLanguageCode() {

		$interlanguageLink = new InterlanguageLink( null, 'Foo' );

		$languageTargetLinks = array(
			'bo' => 'Bar'
		);

		$instance = new LanguageTargetLinksCache( new HashBagOStuff() );
		$instance->saveLanguageTargetLinksToCache( $interlanguageLink, $languageTargetLinks );

		$this->assertEquals(
			$languageTargetLinks,
			$instance->getLanguageTargetLinksFromCache( $interlanguageLink )
		);
	}

	public function testTryToGetLanguageTargetLinksFromEmptyLinksCache() {

		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );
		$languageTargetLinks = array();

		$instance = new LanguageTargetLinksCache( new HashBagOStuff() );
		$instance->saveLanguageTargetLinksToCache( $interlanguageLink, $languageTargetLinks );

		$this->assertFalse(
			$instance->getLanguageTargetLinksFromCache( $interlanguageLink )
		);
	}

	public function testDeleteLanguageTargetLinksToMatchAvailableCache() {

		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );

		$languageTargetLinks = array(
			'bo' => 'Help:Bar',
			'en' => Title::newFromText( 'Foo' )
		);

		$instance = new LanguageTargetLinksCache( new HashBagOStuff() );
		$instance->saveLanguageTargetLinksToCache( $interlanguageLink, $languageTargetLinks );

		$linkReferences = array(
			new DIWikiPage( 'Foo', NS_MAIN )
		);

		$instance->deleteLanguageTargetLinksFromCache( $linkReferences );

		$this->assertFalse(
			$instance->getLanguageTargetLinksFromCache( $interlanguageLink )
		);
	}

	public function testDeletePageLanguageToMatchAvailableCache() {

		$title = Title::newFromText( 'Bar', NS_HELP );
		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );

		$languageTargetLinks = array(
			'bo' => 'Help:Bar',
			'en' => Title::newFromText( 'Foo' )
		);

		$cache = new HashBagOStuff();

		$instance = new LanguageTargetLinksCache( $cache );
		$instance->saveLanguageTargetLinksToCache( $interlanguageLink, $languageTargetLinks );

		$this->assertEquals(
			'bo',
			$instance->getPageLanguageFromCache( $title )
		);

		$instance->deletePageLanguageForTargetFromCache( $title );

		$this->assertFalse(
			$instance->getPageLanguageFromCache( $title )
		);

		$this->assertEquals(
			'en',
			$instance->getPageLanguageFromCache( Title::newFromText( 'Foo' ) )
		);
	}

	public function testNotDeleteLanguageTargetLinksForNotMatchedCacheEntry() {

		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );

		$languageTargetLinks = array(
			'bo' => 'Bar',
			'en' => Title::newFromText( 'Foo' )
		);

		$instance = new LanguageTargetLinksCache( new HashBagOStuff() );
		$instance->saveLanguageTargetLinksToCache( $interlanguageLink, $languageTargetLinks );

		$linkReferences = array(
			new DIWikiPage( 'canNotBeMatched', NS_MAIN ),
			Title::newFromText( 'invalidMatch' ),
			'invalidMatch'
		);

		$instance->deleteLanguageTargetLinksFromCache( $linkReferences );

		$this->assertEquals(
			$languageTargetLinks,
			$instance->getLanguageTargetLinksFromCache( $interlanguageLink )
		);
	}

}
