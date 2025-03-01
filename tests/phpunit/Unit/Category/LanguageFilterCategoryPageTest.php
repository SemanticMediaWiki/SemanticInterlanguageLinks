<?php

namespace SIL\Tests\Category;

use SIL\Category\LanguageFilterCategoryPage;

/**
 * @covers \SIL\Category\LanguageFilterCategoryPage
 * @group semantic-interlanguage-links
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class LanguageFilterCategoryPageTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$LanguageFilterCategoryPage = $this->getMockBuilder( '\SIL\Category\LanguageFilterCategoryPage' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\Category\LanguageFilterCategoryPage',
			$LanguageFilterCategoryPage
		);
	}

	public function testDisabledCategoryFilter() {
		$instance = new LanguageFilterCategoryPage( \Title::newFromText( 'Foo', NS_CATEGORY ) );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$instance->isCategoryFilterByLanguage( false );

		$article = '';

		$instance->modifyCategoryView( $article, $interlanguageLinksLookup );

		$this->assertEmpty(
			$article
		);
	}

	public function testDisabledForNonCategoryNamespace() {
		$instance = new LanguageFilterCategoryPage( \Title::newFromText( 'Foo', NS_MAIN ) );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$instance->isCategoryFilterByLanguage( true );

		$article = '';

		$instance->modifyCategoryView( $article, $interlanguageLinksLookup );

		$this->assertEmpty(
			$article
		);
	}

	public function testEnabledCategoryFilter() {
		$title = \Title::newFromText( 'Foo', NS_CATEGORY );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->once() )
				->method( 'hasSilAnnotationFor' )
				->willReturn( true );

		$context = $this->getMockBuilder( '\IContextSource' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new LanguageFilterCategoryPage( $title );

		$instance->setContext( $context );
		$instance->isCategoryFilterByLanguage( true );

		$article = '';

		$instance->modifyCategoryView( $article, $interlanguageLinksLookup );

		$this->assertInstanceOf(
			'\SIL\Category\LanguageFilterCategoryPage',
			$article
		);

		$this->assertSame(
			$title->interlanguageLinksLookup,
			$interlanguageLinksLookup
		);
	}

	public function testInfoMessageByOpenShowCategoryForEnabledLanguageFilter() {
		$title = \Title::newFromText( 'Foo', NS_CATEGORY );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->atLeastOnce() )
				->method( 'hasSilAnnotationFor' )
				->willReturn( true );

		$interlanguageLinksLookup->expects( $this->once() )
				->method( 'findPageLanguageForTarget' )
				->willReturn( 'foo' );

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$context = $this->getMockBuilder( '\IContextSource' )
			->disableOriginalConstructor()
			->getMock();

		$context->expects( $this->once() )
				->method( 'getOutput' )
				->willReturn( $outputPage );

		$instance = new LanguageFilterCategoryPage( $title );

		$instance->setContext( $context );
		$instance->isCategoryFilterByLanguage( true );

		$article = '';

		$instance->modifyCategoryView( $article, $interlanguageLinksLookup );

		$instance->openShowCategory();
	}

	public function testNoInfoMessageByOpenShowCategoryForNonAvailableLanguage() {
		$title = \Title::newFromText( 'Foo', NS_CATEGORY );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->never() )
				->method( 'findPageLanguageForTarget' )
				->willReturn( false );

		$context = $this->getMockBuilder( '\IContextSource' )
			->disableOriginalConstructor()
			->getMock();

		$context->expects( $this->never() )
				->method( 'getOutput' );

		$instance = new LanguageFilterCategoryPage( $title );

		$instance->setContext( $context );
		$instance->isCategoryFilterByLanguage( true );

		$instance->openShowCategory();
	}

	public function testNoInfoMessageByOpenShowCategory() {
		$title = \Title::newFromText( 'Foo', NS_CATEGORY );

		$context = $this->getMockBuilder( '\IContextSource' )
			->disableOriginalConstructor()
			->getMock();

		$context->expects( $this->never() )
				->method( 'getOutput' );

		$instance = new LanguageFilterCategoryPage( $title );

		$instance->setContext( $context );
		$instance->openShowCategory();
	}

}
