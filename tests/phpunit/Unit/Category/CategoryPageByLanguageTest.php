<?php

namespace SIL\Tests\Category;

use SIL\Category\CategoryPageByLanguage;

/**
 * @covers \SIL\Category\CategoryPageByLanguage
 *
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CategoryPageByLanguageTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$categoryPageByLanguage = $this->getMockBuilder( '\SIL\Category\CategoryPageByLanguage' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\Category\CategoryPageByLanguage',
			$categoryPageByLanguage
		);
	}

	public function testDisabledCategoryFilter() {

		$instance = new CategoryPageByLanguage( \Title::newFromText( 'Foo', NS_CATEGORY ) );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$instance->setCategoryFilterByLanguageState( false );

		$article = '';

		$instance->modifyCategoryView( $article, $interlanguageLinksLookup );

		$this->assertEmpty(
			$article
		);
	}

	public function testDisabledForNonCategoryNamespace() {

		$instance = new CategoryPageByLanguage( \Title::newFromText( 'Foo', NS_MAIN ) );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$instance->setCategoryFilterByLanguageState( true );

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

		$context = $this->getMockBuilder( '\IContextSource' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new CategoryPageByLanguage( $title );

		$instance->setContext( $context );
		$instance->setCategoryFilterByLanguageState( true );

		$article = '';

		$instance->modifyCategoryView( $article, $interlanguageLinksLookup );

		$this->assertInstanceOf(
			'\SIL\Category\CategoryPageByLanguage',
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

		$interlanguageLinksLookup->expects( $this->exactly( 2 ) )
				->method( 'findPageLanguageForTarget' )
				->will( $this->returnValue( 'foo' ) );

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$context = $this->getMockBuilder( '\IContextSource' )
			->disableOriginalConstructor()
			->getMock();

		$context->expects( $this->once() )
				->method( 'getOutput' )
				->will( $this->returnValue( $outputPage ) );

		$instance = new CategoryPageByLanguage( $title );

		$instance->setContext( $context );
		$instance->setCategoryFilterByLanguageState( true );

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
				->will( $this->returnValue( false ) );

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$context = $this->getMockBuilder( '\IContextSource' )
			->disableOriginalConstructor()
			->getMock();

		$context->expects( $this->never() )
				->method( 'getOutput' );

		$instance = new CategoryPageByLanguage( $title );

		$instance->setContext( $context );
		$instance->setCategoryFilterByLanguageState( true );

		$instance->openShowCategory();
	}

	public function testNoInfoMessageByOpenShowCategory() {

		$title = \Title::newFromText( 'Foo', NS_CATEGORY );

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$context = $this->getMockBuilder( '\IContextSource' )
			->disableOriginalConstructor()
			->getMock();

		$context->expects( $this->never() )
				->method( 'getOutput' );

		$instance = new CategoryPageByLanguage( $title );

		$instance->setContext( $context );
		$instance->openShowCategory();
	}

}
