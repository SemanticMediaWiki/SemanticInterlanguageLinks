<?php

namespace SIL\Tests;

use SIL\PageContentLanguageDbModifier;
use Title;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \SIL\PageContentLanguageDbModifier
 * @group semantic-interlanguage-links
 *
 * @license GPL-2.0-or-later
 * @since 1.3
 *
 * @author mwjames
 */
class PageContentLanguageDbModifierTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\PageContentLanguageDbModifier',
			new PageContentLanguageDbModifier( $title )
		);
	}

	public function testNotMarkedAsPageLanguageByDB() {
		$title = Title::newFromText( __METHOD__ );

		$connection = $this->getMockBuilder( '\Wikimedia\Rdbms\Database' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$instance = new PageContentLanguageDbModifier(
			$title,
			$connection
		);

		$instance->markAsPageLanguageByDB( false );

		$this->assertNull(
			$instance->updatePageLanguage( 'en' )
		);
	}

	public function testForceUpdateOfPageLanguageOnDifferentLanguageCode() {
		$title = Title::newFromText( __METHOD__ );

		$connection = $this->getMockBuilder( '\Wikimedia\Rdbms\Database' )
			->disableOriginalConstructor()
			->onlyMethods( [ 'update' ] )
			->getMockForAbstractClass();

		$connection->expects( $this->once() )
			->method( 'update' );

		if ( version_compare( MW_VERSION, '1.39', '>' ) ) {
			$wdb = TestingAccessWrapper::newFromObject( $connection );
			$wdb->flagsHolder = new \Wikimedia\Rdbms\Database\DatabaseFlags( 0 );
		}

		$linkCache = $this->getMockBuilder( '\LinkCache' )
			->disableOriginalConstructor()
			->getMock();

		$linkCache->expects( $this->once() )
			->method( 'getGoodLinkFieldObj' )
			->willReturn( 'fr' );

		$instance = new PageContentLanguageDbModifier(
			$title,
			$connection,
			$linkCache
		);

		$instance->markAsPageLanguageByDB( true );
		$instance->updatePageLanguage( 'en' );
	}

	public function testNoUpdateOnSameLanguageCode() {
		$title = Title::newFromText( __METHOD__ );

		$connection = $this->getMockBuilder( '\Wikimedia\Rdbms\Database' )
			->disableOriginalConstructor()
			->onlyMethods( [ 'update' ] )
			->getMockForAbstractClass();

		$connection->expects( $this->never() )
			->method( 'update' );

		$linkCache = $this->getMockBuilder( '\LinkCache' )
			->disableOriginalConstructor()
			->getMock();

		$linkCache->expects( $this->once() )
			->method( 'getGoodLinkFieldObj' )
			->willReturn( 'en' );

		$instance = new PageContentLanguageDbModifier(
			$title,
			$connection,
			$linkCache
		);

		$instance->markAsPageLanguageByDB( true );
		$instance->updatePageLanguage( 'en' );
	}

}
