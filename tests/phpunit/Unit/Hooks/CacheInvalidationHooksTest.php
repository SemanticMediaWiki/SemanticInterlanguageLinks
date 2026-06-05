<?php

namespace SIL\Tests\Hooks;

use MediaWiki\Title\Title;
use SIL\Hooks\CacheInvalidationHooks;
use SIL\InterlanguageLinksLookup;
use SMW\Store;
use WikiPage;

/**
 * @covers \SIL\Hooks\CacheInvalidationHooks
 * @group semantic-interlanguage-links
 *
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */
class CacheInvalidationHooksTest extends \PHPUnit\Framework\TestCase {

	private $interlanguageLinksLookup;

	protected function setUp(): void {
		parent::setUp();

		$this->interlanguageLinksLookup = $this->getMockBuilder( InterlanguageLinksLookup::class )
			->disableOriginalConstructor()
			->getMock();
	}

	private function newInstance(): CacheInvalidationHooks {
		return new CacheInvalidationHooks( $this->interlanguageLinksLookup );
	}

	private function newWikiPageWithTitle( Title $title ): WikiPage {
		$wikiPage = $this->getMockBuilder( WikiPage::class )
			->disableOriginalConstructor()
			->getMock();

		$wikiPage->expects( $this->any() )
			->method( 'getTitle' )
			->willReturn( $title );

		return $wikiPage;
	}

	private function newStore(): Store {
		return $this->getMockBuilder( Store::class )
			->disableOriginalConstructor()
			->getMockForAbstractClass();
	}

	public function testCanConstruct(): void {
		$this->assertInstanceOf(
			CacheInvalidationHooks::class,
			$this->newInstance()
		);
	}

	public function testOnArticlePurge(): void {
		$wikiPage = $this->newWikiPageWithTitle( Title::newFromText( __METHOD__ ) );

		$this->assertTrue(
			$this->newInstance()->onArticlePurge( $wikiPage )
		);
	}

	public function testOnRevisionFromEditComplete(): void {
		$wikiPage = $this->newWikiPageWithTitle( Title::newFromText( __METHOD__ ) );
		$tags = [];

		$this->assertTrue(
			$this->newInstance()->onRevisionFromEditComplete( $wikiPage, null, null, null, $tags )
		);
	}

	public function testOnSMWSQLStoreBeforeDeleteSubjectComplete(): void {
		$title = Title::newFromText( __METHOD__ );

		$this->assertTrue(
			$this->newInstance()->onSMW__SQLStore__BeforeDeleteSubjectComplete( $this->newStore(), $title )
		);
	}

	public function testOnSMWSQLStoreBeforeChangeTitleComplete(): void {
		$title = Title::newFromText( __METHOD__ );

		$this->assertTrue(
			$this->newInstance()->onSMW__SQLStore__BeforeChangeTitleComplete( $this->newStore(), $title, $title, 0, 0 )
		);
	}

}
