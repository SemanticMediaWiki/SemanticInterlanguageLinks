<?php

namespace SIL\Tests\Hooks;

use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use SIL\Hooks\PageLanguageHooks;
use SIL\InterlanguageLinksLookup;
use SIL\PageContentLanguageOnTheFlyModifier;

/**
 * @covers \SIL\Hooks\PageLanguageHooks
 * @group semantic-interlanguage-links
 *
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */
class PageLanguageHooksTest extends \PHPUnit\Framework\TestCase {

	private $interlanguageLinksLookup;
	private $pageContentLanguageOnTheFlyModifier;

	protected function setUp(): void {
		parent::setUp();

		$this->interlanguageLinksLookup = $this->getMockBuilder( InterlanguageLinksLookup::class )
			->disableOriginalConstructor()
			->getMock();

		$this->pageContentLanguageOnTheFlyModifier = $this->getMockBuilder( PageContentLanguageOnTheFlyModifier::class )
			->disableOriginalConstructor()
			->getMock();
	}

	private function newInstance(): PageLanguageHooks {
		return new PageLanguageHooks(
			$this->interlanguageLinksLookup,
			$this->pageContentLanguageOnTheFlyModifier
		);
	}

	public function testCanConstruct(): void {
		$this->assertInstanceOf(
			PageLanguageHooks::class,
			$this->newInstance()
		);
	}

	public function testOnArticleFromTitle(): void {
		$GLOBALS['silgEnabledCategoryFilterByLanguage'] = true;

		$title = Title::newFromText( __METHOD__ );
		$article = '';
		$context = null;

		$this->assertTrue(
			$this->newInstance()->onArticleFromTitle( $title, $article, $context )
		);
	}

	public function testOnPageContentLanguage(): void {
		$this->pageContentLanguageOnTheFlyModifier->expects( $this->once() )
			->method( 'getPageContentLanguage' )
			->willReturn( 'en' );

		$title = Title::newFromText( __METHOD__ );
		$languageFactory = MediaWikiServices::getInstance()->getLanguageFactory();

		// Start from a different language so the by-reference replacement is observable.
		$pageLang = $languageFactory->getLanguage( 'fr' );
		$userLang = $languageFactory->getLanguage( 'en' );

		$this->assertTrue(
			$this->newInstance()->onPageContentLanguage( $title, $pageLang, $userLang )
		);

		$this->assertEquals(
			'en',
			$pageLang->getCode()
		);
	}

	public function testOnSkinTemplateGetLanguageLink(): void {
		$title = Title::newFromText( __METHOD__ );
		$languageLink = [];

		$this->assertTrue(
			$this->newInstance()->onSkinTemplateGetLanguageLink( $languageLink, $title, $title, null )
		);
	}

}
