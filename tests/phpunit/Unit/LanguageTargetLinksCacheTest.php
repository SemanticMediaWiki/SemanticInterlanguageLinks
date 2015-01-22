<?php

namespace SIL\Tests;

use SIL\LanguageTargetLinksCache;
use SIL\InterlanguageLink;

use SMW\DIWikiPage;

use Onoi\Cache\CacheFactory;

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

	private $cache;

	protected function setUp() {
		parent::setUp();

		$this->cache = CacheFactory::getInstance()->newMediaWikiCache( new HashBagOStuff() );
	}

	public function testCanConstruct() {

		$cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$this->assertInstanceOf(
			'\SIL\LanguageTargetLinksCache',
			new LanguageTargetLinksCache( $cache )
		);
	}

	/**
	 * @dataProvider pageLanguageCacheStrategyProvider
	 */
	public function testRoundtrip( $pageLanguageCacheStrategy ) {

		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );

		$languageTargetLinks = array(
			'bo' => 'Bar',
			'en' => Title::newFromText( 'Foo' )
		);

		$instance = new LanguageTargetLinksCache( $this->cache );
		$instance->setPageLanguageCacheStrategy( $pageLanguageCacheStrategy );

		$instance->saveLanguageTargetLinksToCache(
			$interlanguageLink,
			$languageTargetLinks
		);

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

	/**
	 * @dataProvider pageLanguageCacheStrategyProvider
	 */
	public function testTryToGetLanguageTargetLinksForUnknownLanguageCode( $pageLanguageCacheStrategy ) {

		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );

		$languageTargetLinks = array(
			'bo' => 'Bar'
		);

		$instance = new LanguageTargetLinksCache( $this->cache );
		$instance->setPageLanguageCacheStrategy( $pageLanguageCacheStrategy );

		$instance->saveLanguageTargetLinksToCache(
			$interlanguageLink,
			$languageTargetLinks
		);

		$this->assertFalse(
			$instance->getLanguageTargetLinksFromCache( $interlanguageLink )
		);
	}

	/**
	 * @dataProvider pageLanguageCacheStrategyProvider
	 */
	public function testTryToGetLanguageTargetLinksForNullLanguageCode( $pageLanguageCacheStrategy ) {

		$interlanguageLink = new InterlanguageLink( null, 'Foo' );

		$languageTargetLinks = array(
			'bo' => 'Bar'
		);

		$instance = new LanguageTargetLinksCache( $this->cache );
		$instance->setPageLanguageCacheStrategy( $pageLanguageCacheStrategy );

		$instance->saveLanguageTargetLinksToCache(
			$interlanguageLink,
			$languageTargetLinks
		);

		$this->assertEquals(
			$languageTargetLinks,
			$instance->getLanguageTargetLinksFromCache( $interlanguageLink )
		);
	}

	/**
	 * @dataProvider pageLanguageCacheStrategyProvider
	 */
	public function testTryToGetLanguageTargetLinksFromEmptyLinksCache( $pageLanguageCacheStrategy ) {

		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );
		$languageTargetLinks = array();

		$instance = new LanguageTargetLinksCache( $this->cache );
		$instance->setPageLanguageCacheStrategy( $pageLanguageCacheStrategy );

		$instance->saveLanguageTargetLinksToCache(
			$interlanguageLink,
			$languageTargetLinks
		);

		$this->assertFalse(
			$instance->getLanguageTargetLinksFromCache( $interlanguageLink )
		);
	}

	/**
	 * @dataProvider pageLanguageCacheStrategyProvider
	 */
	public function testDeleteLanguageTargetLinks( $pageLanguageCacheStrategy ) {

		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );

		$languageTargetLinks = array(
			'bo' => 'Help:Bar',
			'en' => Title::newFromText( 'Foo' )
		);

		$instance = new LanguageTargetLinksCache( $this->cache );
		$instance->setPageLanguageCacheStrategy( $pageLanguageCacheStrategy );

		$instance->saveLanguageTargetLinksToCache(
			$interlanguageLink,
			$languageTargetLinks
		);

		$linkReferences = array(
			new DIWikiPage( 'Foo', NS_MAIN )
		);

		$instance->deleteLanguageTargetLinksFromCache( $linkReferences );

		$this->assertFalse(
			$instance->getLanguageTargetLinksFromCache( $interlanguageLink )
		);
	}

	/**
	 * @dataProvider pageLanguageCacheStrategyProvider
	 */
	public function testDeletePageLanguageForMatchedTarget( $pageLanguageCacheStrategy ) {

		$helpNS = $GLOBALS['wgContLang']->getNsText( NS_HELP );

		$title = Title::newFromText( 'Bar', NS_HELP );
		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );

		$languageTargetLinks = array(
			'bo' => "$helpNS:Bar",
			'en' => Title::newFromText( 'Foo' )
		);

		$instance = new LanguageTargetLinksCache( $this->cache );
		$instance->setPageLanguageCacheStrategy( $pageLanguageCacheStrategy );

		$instance->saveLanguageTargetLinksToCache(
			$interlanguageLink,
			$languageTargetLinks
		);

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

	/**
	 * @dataProvider pageLanguageCacheStrategyProvider
	 */
	public function testNoLanguageTargetLinksDeleteForNonMatchedTarget( $pageLanguageCacheStrategy ) {

		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );

		$languageTargetLinks = array(
			'bo' => 'Bar',
			'en' => Title::newFromText( 'Foo' )
		);

		$instance = new LanguageTargetLinksCache( $this->cache );
		$instance->setPageLanguageCacheStrategy( $pageLanguageCacheStrategy );

		$instance->saveLanguageTargetLinksToCache(
			$interlanguageLink,
			$languageTargetLinks
		);

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

	/**
	 * @dataProvider pageLanguageCacheStrategyProvider
	 */
	public function testUpdatePageLanguageToCache( $pageLanguageCacheStrategy ) {

		// If the VERSION changed then the id's need to be changed as well
		if ( $pageLanguageCacheStrategy !== 'blob' ) {
			$id = 'foo:p:';
			$data = 'bo';
		} else{
			$id = 'foo:b:';
			$data = array( 'foo:p:ddc35f88fa71b6ef142ae61f35364653' => 'bo' );
		}

		$title = Title::newFromText( 'Bar', NS_MAIN );

		$cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$cache->expects( $this->once() )
			->method( 'save' )
			->with(
				$this->stringContains( $id ) ,
				$this->equalTo( $data ) );

		$instance = new LanguageTargetLinksCache( $cache );
		$instance->setPageLanguageCacheStrategy( $pageLanguageCacheStrategy );
		$instance->setCachePrefix( 'foo:' );

		$instance->updatePageLanguageToCache( $title, 'bo' );
	}

	public function pageLanguageCacheStrategyProvider() {

		$provider = array(
			array( 'blob' ),
			array( 'non-blob' )
		);

		return $provider;
	}

}
