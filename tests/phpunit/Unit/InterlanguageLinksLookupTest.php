<?php

namespace SIL\Tests;

use SIL\InterlanguageLinksLookup;
use SIL\InterlanguageLink;
use SMW\DataValueFactory;
use SMW\DIWikiPage;
use SMW\PropertyRegistry;
use SMWDIBlob as DIBlob;
use Title;

/**
 * @covers \SIL\InterlanguageLinksLookup
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class InterlanguageLinksLookupTest extends \PHPUnit_Framework_TestCase {

	private $store;

	protected function setUp(): void {
		parent::setUp();

		$this->store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();
	}

	public function testCanConstruct() {

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\InterlanguageLinksLookup',
			new InterlanguageLinksLookup( $languageTargetLinksCache )
		);
	}

	public function testRedirectTargetFor() {

		$title = Title::newFromText( __METHOD__ );

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->setMethods( [ 'getRedirectTarget' ] )
			->getMockForAbstractClass();

		$store->expects( $this->once() )
			->method( 'getRedirectTarget' )
			->with( $this->equalTo( DIWikiPage::newFromTitle( $title ) ) )
			->will( $this->returnValue( DIWikiPage::newFromTitle( $title ) ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $store );

		$this->assertEquals(
			$title,
			$instance->getRedirectTargetFor( $title )
		);
	}

	public function testFindValidPageLanguageForTarget() {

		$title = Title::newFromText( __METHOD__ );

		$verifyPropertyTypeId = function( $property ) {
			return $property->findPropertyTypeID() === '_txt';
		};

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->at( 0 ) )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIWikiPage::newFromTitle( $title ) ) )
			->will( $this->returnValue( [ new DIWikiPage( 'Foo', NS_MAIN ) ] ) );

		$store->expects( $this->at( 1 ) )
			->method( 'getPropertyValues' )
			->with(
				$this->equalTo( new DIWikiPage( 'Foo', NS_MAIN ) ),
				$this->callback( $verifyPropertyTypeId ) )
			->will( $this->returnValue( [ new DIBlob( 'en' ), new DIBlob( 'ja' ) ] ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $store );

		$this->assertEquals(
			'ja',
			$instance->findPageLanguageForTarget( $title )
		);
	}

	public function testInvalidValueToFindNoPageLanguageForTarget() {

		$title = Title::newFromText( __METHOD__ );

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->at( 0 ) )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIWikiPage::newFromTitle( $title ) ) )
			->will( $this->returnValue( [ new DIWikiPage( 'Foo', NS_MAIN ) ] ) );

		$store->expects( $this->at( 1 ) )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( new DIWikiPage( 'Foo', NS_MAIN ) ) )
			->will( $this->returnValue( [ new DIWikiPage( 'invalid', NS_MAIN ) ] ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $store );

		$this->assertEquals(
			InterlanguageLinksLookup::NO_LANG,
			$instance->findPageLanguageForTarget( $title )
		);
	}

	public function testFindNoPageLanguageForTarget() {

		$title = Title::newFromText( __METHOD__ );

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->at( 0 ) )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIWikiPage::newFromTitle( $title ) ) )
			->will( $this->returnValue( [] ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $store );

		$this->assertEquals(
			InterlanguageLinksLookup::NO_LANG,
			$instance->findPageLanguageForTarget( $title )
		);
	}

	public function testFindPageLanguageForTargetFromCache() {

		$target = Title::newFromText( __METHOD__ );

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$languageTargetLinksCache->expects( $this->once() )
			->method( 'getPageLanguageFromCache' )
			->with( $this->equalTo( $target ) )
			->will( $this->returnValue( 'foo' ) );

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $store );

		$this->assertEquals(
			'foo',
			$instance->findPageLanguageForTarget( $target )
		);
	}

	public function testFindFullListOfReferenceTargetLinksSpecificTarget() {

		$title = Title::newFromText( __METHOD__ );

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->at( 0 ) )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIWikiPage::newFromTitle( $title ) ) )
			->will( $this->returnValue( [ new DIWikiPage( 'Foo', NS_MAIN ) ] ) );

		$store->expects( $this->at( 1 ) )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( new DIWikiPage( 'Foo', NS_MAIN ) ) )
			->will( $this->returnValue( [ new DIWikiPage( 'Bar', NS_MAIN ) ] ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $store );

		$this->assertEquals(
			[ new DIWikiPage( 'Bar', NS_MAIN ) ],
			$instance->findFullListOfReferenceTargetLinks( $title )
		);
	}

	public function testFindNoLinkReferencesForTarget() {

		$title = Title::newFromText( __METHOD__ );

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->at( 0 ) )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIWikiPage::newFromTitle( $title ) ) )
			->will( $this->returnValue( [] ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $store );

		$this->assertEmpty(
			$instance->findFullListOfReferenceTargetLinks( $title )
		);
	}

	public function testVerifyQueryStringByQueryingLanguageTargetLinks() {

		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );

		$verifyQueryCallback = function( $query ) {
			return $query->getQueryString() === '[[Interlanguage reference::Foo]]';
		};

		$queryResult = $this->getMockBuilder( '\SMWQueryResult' )
			->disableOriginalConstructor()
			->getMock();

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->once() )
			->method( 'getQueryResult' )
			->with( $this->callback( $verifyQueryCallback ) )
			->will( $this->returnValue( $queryResult ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $store );

		$instance->queryLanguageTargetLinks( $interlanguageLink );
	}

	public function testQueryLanguageTargetLinks() {

		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );

		$blobValue = DataValueFactory::getInstance()->newDataValueByType( '_txt' );
		$blobValue->setUserValue( 'vi' );

		$resultArray = $this->getMockBuilder( '\SMWResultArray' )
			->disableOriginalConstructor()
			->getMock();

		$resultArray->expects( $this->any() )
			->method( 'getNextDataValue' )
			->will( $this->onConsecutiveCalls( $blobValue, false )  );

		$resultArray->expects( $this->any() )
			->method( 'getResultSubject' )
			->will( $this->returnValue( new DIWikiPage( 'Bar', NS_MAIN ) ) );

		$queryResult = $this->getMockBuilder( '\SMWQueryResult' )
			->disableOriginalConstructor()
			->getMock();

		$queryResult->expects( $this->any() )
			->method( 'getNext' )
			->will( $this->onConsecutiveCalls(
				[ $resultArray ],
				[ $resultArray ],
				false ) );

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->once() )
			->method( 'getQueryResult' )
			->will( $this->returnValue( $queryResult ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $store );

		$instance->queryLanguageTargetLinks( $interlanguageLink );
	}

	public function testQueryLanguageTargetLinksContainsCurrentTargetOnly() {

		$currentTarget = Title::newFromText( 'Bar' );
		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );

		$queryResult = $this->getMockBuilder( '\SMWQueryResult' )
			->disableOriginalConstructor()
			->getMock();

		$queryResult->expects( $this->any() )
			->method( 'getNext' )
			->will( $this->onConsecutiveCalls( false ) );

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->once() )
			->method( 'getQueryResult' )
			->will( $this->returnValue( $queryResult ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $store );

		$expected = [
			'en' => 'Bar'
		];

		$this->assertEquals(
			$expected,
			$instance->queryLanguageTargetLinks( $interlanguageLink, $currentTarget )
		);
	}

	public function testGetLanguageTargetLinksFromCache() {

		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );

		$languageTargetLinks = [
			'bo' => 'Bar',
			'en' => Title::newFromText( 'Foo' )
		];

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$languageTargetLinksCache->expects( $this->once() )
			->method( 'getLanguageTargetLinksFromCache' )
			->with( $this->equalTo( $interlanguageLink ) )
			->will( $this->returnValue( $languageTargetLinks ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->queryLanguageTargetLinks( $interlanguageLink );
	}

	public function testTryCachedPageLanguageForTarget() {

		$target = Title::newFromText( 'Foo' );

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$languageTargetLinksCache->expects( $this->once() )
			->method( 'getPageLanguageFromCache' )
			->with( $this->equalTo( $target ) )
			->will( $this->returnValue( 'en' ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );

		$this->assertEquals(
			'en',
			$instance->findPageLanguageForTarget( $target )
		);
	}
/*
	public function testTryFalseCachedPageLanguageForTarget() {

		$target = Title::newFromText( 'Foo' );

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$languageTargetLinksCache->expects( $this->once() )
			->method( 'getPageLanguageFromCache' )
			->with( $this->equalTo( $target ) )
			->will( $this->returnValue( false ) );

		$languageTargetLinksCache->expects( $this->once() )
			->method( 'pushPageLanguageToCache' );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->findPageLanguageForTarget( $target );
	}
*/

	public function testInvalidateCachedLanguageTargetLinks() {

		$target = Title::newFromText( 'Foo' );

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$languageTargetLinksCache->expects( $this->once() )
			->method( 'deleteLanguageTargetLinksFromCache' );

		$languageTargetLinksCache->expects( $this->once() )
			->method( 'deletePageLanguageForTargetFromCache' )
			->with( $this->equalTo( $target ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $this->store );

		$instance->resetLookupCacheBy( $target );
	}

	public function testTryLookupForUngregisteredProperty() {

		PropertyRegistry::clear();

		$target = Title::newFromText( 'Foo' );

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$languageTargetLinksCache->expects( $this->atLeastOnce() )
			->method( 'getPageLanguageFromCache' );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $this->store );

		$this->assertEquals(
			InterlanguageLinksLookup::NO_LANG,
			$instance->findPageLanguageForTarget( $target )
		);

		PropertyRegistry::clear();
	}

	public function testTryFindListOfReferenceTargetLinksForUngregisteredProperty() {

		PropertyRegistry::clear();

		$target = Title::newFromText( 'Foo' );

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $this->store );

		$this->assertEmpty(
			$instance->findFullListOfReferenceTargetLinks( $target )
		);

		PropertyRegistry::clear();
	}

}
