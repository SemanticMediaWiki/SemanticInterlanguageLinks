<?php

namespace SIL\Tests\Category;

use ContentHandler;
use MediaWiki\MediaWikiServices;
use RequestContext;
use SIL\Category\LanguageFilterCategoryViewer;
use Title;
use WikiPage;

/**
 * @covers \SIL\Category\LanguageFilterCategoryViewer
 * @group semantic-interlanguage-links
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class LanguageFilterCategoryViewerTest extends \PHPUnit\Framework\TestCase {

	private $context;

	protected function setUp(): void {
		parent::setUp();

		$config = $this->getMockBuilder( '\Config' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$this->context = $this->getMockBuilder( '\IContextSource' )
			->disableOriginalConstructor()
			->getMock();

		$this->context->expects( $this->any() )
				->method( 'getConfig' )
				->willReturn( $config );

		$this->context->expects( $this->any() )
				->method( 'getOutput' )
				->willReturn( $outputPage );
	}

	public function testCanConstruct() {
		$title = Title::newFromText( 'Foo', NS_CATEGORY );

		$this->assertInstanceOf(
			'\SIL\Category\LanguageFilterCategoryViewer',
			new LanguageFilterCategoryViewer( $title, $this->context )
		);
	}

	public function testAddPageForNoInterlanguageLinksLookup() {
		$title = Title::newFromText( 'Foo', NS_CATEGORY );
		$target = Title::newFromText( 'Bar' );

		$instance = new LanguageFilterCategoryViewer(
			$title,
			$this->context
		);

		$instance->addPage( $target, 'B', 0 );

		$this->assertNotEmpty(
			$instance->articles
		);
	}

	public function testAddImageForNoInterlanguageLinksLookup() {
		$title = Title::newFromText( 'Foo', NS_CATEGORY );
		$target = Title::newFromText( 'Bar', NS_FILE );

		$instance = new LanguageFilterCategoryViewer(
			$title,
			$this->context
		);

		$instance->addImage( $target, 'B', 0 );

		$this->assertNotEmpty(
			$instance->imgsNoGallery
		);
	}

	public function testTryAddImageForNoLanguageMatch() {
		$title = Title::newFromText( 'Foo', NS_CATEGORY );
		$target = Title::newFromText( 'Bar', NS_FILE );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->atLeastOnce() )
				->method( 'hasSilAnnotationFor' )
				->willReturn( true );

		$interlanguageLinksLookup->expects( $this->at( 0 ) )
			->method( 'findPageLanguageForTarget' )
			->willReturn( 'no' );

		$interlanguageLinksLookup->expects( $this->at( 1 ) )
			->method( 'findPageLanguageForTarget' )
			->willReturn( 'match' );

		$title->interlanguageLinksLookup = $interlanguageLinksLookup;

		$instance = new LanguageFilterCategoryViewer(
			$title,
			$this->context
		);

		$instance->addImage( $target, 'B', 0 );

		$this->assertEmpty(
			$instance->imgsNoGallery
		);
	}

	public function testAddSubcategoryForNoInterlanguageLinksLookup() {
		$title = Title::newFromText( 'Foo', NS_CATEGORY );

		// We have to create the page now in order for
		// getPageByReference() to return true.
		// This is due to https://github.com/wikimedia/mediawiki/commit/c259c37033d3f16aa949855d25178e76578bb586
		$existingPage = $this->getExistingTestPage( $title );
		$identity = $existingPage->getTitle()->toPageIdentity();

		$category = $this->getMockBuilder( '\Category' )
			->disableOriginalConstructor()
			->getMock();

		$category->expects( $this->any() )
			->method( 'getTitle' )
			->willReturn( Title::newFromText( 'Bar' ) );

		$category->expects( $this->any() )
			->method( 'getPage' )
			->willReturn( $identity );

		$instance = new LanguageFilterCategoryViewer(
			$title,
			$this->context
		);

		$instance->addSubcategoryObject( $category, 'B', '' );

		$this->assertNotEmpty(
			$instance->children
		);
	}

	/**
	 * Returns a WikiPage representing an existing page.
	 *
	 * From https://github.com/stronk7/mediawiki/blob/master/tests/phpunit/MediaWikiIntegrationTestCase.php#L250
	 * With modifications
	 *
	 * @param Title $title
	 * @return WikiPage
	 */
	protected function getExistingTestPage( Title $title ) {
		$page = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );

		// If page doesn't exist, create it.
		if ( !$page->exists() ) {
			$user = RequestContext::getMain()->getUser();
			$page->doUserEditContent(
				ContentHandler::makeContent(
					'Test content for LFCVT',
					$title,
					// Regardless of how the wiki is configure or what extensions are present,
					// force this page to be a wikitext one.
					CONTENT_MODEL_WIKITEXT
				),
				$user,
				'Summary of LFCVT',
				EDIT_NEW | EDIT_SUPPRESS_RC
			);
		}

		return $page;
	}

	public function testTryAddSubcategoryForNoLanguageMatch() {
		$title = Title::newFromText( 'Foo', NS_CATEGORY );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->atLeastOnce() )
				->method( 'hasSilAnnotationFor' )
				->willReturn( true );

		$interlanguageLinksLookup->expects( $this->at( 0 ) )
			->method( 'findPageLanguageForTarget' )
			->willReturn( 'no' );

		$interlanguageLinksLookup->expects( $this->at( 1 ) )
			->method( 'findPageLanguageForTarget' )
			->willReturn( 'match' );

		$title->interlanguageLinksLookup = $interlanguageLinksLookup;

		$category = $this->getMockBuilder( '\Category' )
			->disableOriginalConstructor()
			->getMock();

		$category->expects( $this->once() )
			->method( 'getTitle' )
			->willReturn( Title::newFromText( 'Bar' ) );

		$instance = new LanguageFilterCategoryViewer(
			$title,
			$this->context
		);

		$instance->addSubcategoryObject( $category, 'B', '' );

		$this->assertEmpty(
			$instance->children
		);
	}

	public function testAddPageForEmptyLanguage() {
		$title = Title::newFromText( 'Foo', NS_CATEGORY );
		$target = Title::newFromText( 'Bar' );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->atLeastOnce() )
				->method( 'hasSilAnnotationFor' )
				->willReturn( true );

		$interlanguageLinksLookup->expects( $this->atLeastOnce() )
			->method( 'findPageLanguageForTarget' )
			->with( $title )
			->willReturn( '' );

		$title->interlanguageLinksLookup = $interlanguageLinksLookup;

		$instance = new LanguageFilterCategoryViewer(
			$title,
			$this->context
		);

		$instance->addPage( $target, 'B', 0 );

		$this->assertNotEmpty(
			$instance->articles
		);
	}

	public function testAddPageForLanguageMatch() {
		$title = Title::newFromText( 'Foo', NS_CATEGORY );
		$target = Title::newFromText( 'Bar' );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->atLeastOnce() )
				->method( 'hasSilAnnotationFor' )
				->willReturn( true );

		$interlanguageLinksLookup->expects( $this->at( 1 ) )
			->method( 'findPageLanguageForTarget' )
			->with( $title )
			->willReturn( 'vi' );

		$interlanguageLinksLookup->expects( $this->at( 2 ) )
			->method( 'findPageLanguageForTarget' )
			->with( $target )
			->willReturn( 'vi' );

		$title->interlanguageLinksLookup = $interlanguageLinksLookup;

		$instance = new LanguageFilterCategoryViewer(
			$title,
			$this->context
		);

		$instance->addPage( $target, 'B', 0 );

		$this->assertNotEmpty(
			$instance->articles
		);
	}

	public function testTryAddPageForNoLanguageMatch() {
		$title = Title::newFromText( 'Foo', NS_CATEGORY );
		$target = Title::newFromText( 'Bar' );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->atLeastOnce() )
				->method( 'hasSilAnnotationFor' )
				->willReturn( true );

		$interlanguageLinksLookup->expects( $this->at( 1 ) )
			->method( 'findPageLanguageForTarget' )
			->with( $title )
			->willReturn( 'vi' );

		$interlanguageLinksLookup->expects( $this->at( 2 ) )
			->method( 'findPageLanguageForTarget' )
			->with( $target )
			->willReturn( 'en' );

		$title->interlanguageLinksLookup = $interlanguageLinksLookup;

		$instance = new LanguageFilterCategoryViewer(
			$title,
			$this->context
		);

		$instance->addPage( $target, 'B', 0 );

		$this->assertEmpty(
			$instance->articles
		);
	}

	public function testTryAddPageForNoAnnotationMatch() {
		$title = Title::newFromText( 'Foo', NS_CATEGORY );
		$target = Title::newFromText( 'Bar' );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->atLeastOnce() )
				->method( 'hasSilAnnotationFor' )
				->willReturn( false );

		$title->interlanguageLinksLookup = $interlanguageLinksLookup;

		$instance = new LanguageFilterCategoryViewer(
			$title,
			$this->context
		);

		$instance->addPage( $target, 'B', 0 );

		$this->assertEmpty(
			$instance->articles
		);
	}

}
