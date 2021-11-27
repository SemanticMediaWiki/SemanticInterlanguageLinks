<?php

namespace SIL\Tests\Category;

use SIL\Category\LanguageFilterCategoryViewer;
use Title;

/**
 * @covers \SIL\Category\LanguageFilterCategoryViewer
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class LanguageFilterCategoryViewerTest extends \PHPUnit_Framework_TestCase {

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
				->will( $this->returnValue( $config ) );

		$this->context->expects( $this->any() )
				->method( 'getOutput' )
				->will( $this->returnValue( $outputPage ) );
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

		$instance->addPage( $target, 'B', '' );

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

		$instance->addImage( $target, 'B', '' );

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
				->will( $this->returnValue( true ) );

		$interlanguageLinksLookup->expects( $this->at( 0 ) )
			->method( 'findPageLanguageForTarget' )
			->will( $this->returnValue( 'no' ) );

		$interlanguageLinksLookup->expects( $this->at( 1 ) )
			->method( 'findPageLanguageForTarget' )
			->will( $this->returnValue( 'match' ) );

		$title->interlanguageLinksLookup = $interlanguageLinksLookup;

		$instance = new LanguageFilterCategoryViewer(
			$title,
			$this->context
		);

		$instance->addImage( $target, 'B', '' );

		$this->assertEmpty(
			$instance->imgsNoGallery
		);
	}

	public function testAddSubcategoryForNoInterlanguageLinksLookup() {

		$title = Title::newFromText( 'Foo', NS_CATEGORY );

		$category = $this->getMockBuilder( '\Category' )
			->disableOriginalConstructor()
			->getMock();

		$category->expects( $this->any() )
			->method( 'getTitle' )
			->will( $this->returnValue( Title::newFromText( 'Bar' ) ) );

		$instance = new LanguageFilterCategoryViewer(
			$title,
			$this->context
		);

		$instance->addSubcategoryObject( $category, 'B', '' );

		$this->assertNotEmpty(
			$instance->children
		);
	}

	public function testTryAddSubcategoryForNoLanguageMatch() {

		$title = Title::newFromText( 'Foo', NS_CATEGORY );

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->atLeastOnce() )
				->method( 'hasSilAnnotationFor' )
				->will( $this->returnValue( true ) );

		$interlanguageLinksLookup->expects( $this->at( 0 ) )
			->method( 'findPageLanguageForTarget' )
			->will( $this->returnValue( 'no' ) );

		$interlanguageLinksLookup->expects( $this->at( 1 ) )
			->method( 'findPageLanguageForTarget' )
			->will( $this->returnValue( 'match' ) );

		$title->interlanguageLinksLookup = $interlanguageLinksLookup;

		$category = $this->getMockBuilder( '\Category' )
			->disableOriginalConstructor()
			->getMock();

		$category->expects( $this->once() )
			->method( 'getTitle' )
			->will( $this->returnValue( Title::newFromText( 'Bar' ) ) );

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
				->will( $this->returnValue( true ) );

		$interlanguageLinksLookup->expects( $this->atLeastOnce() )
			->method( 'findPageLanguageForTarget' )
			->with( $this->equalTo( $title ) )
			->will( $this->returnValue( '' ) );

		$title->interlanguageLinksLookup = $interlanguageLinksLookup;

		$instance = new LanguageFilterCategoryViewer(
			$title,
			$this->context
		);

		$instance->addPage( $target, 'B', '' );

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
				->will( $this->returnValue( true ) );

		$interlanguageLinksLookup->expects( $this->at( 1 ) )
			->method( 'findPageLanguageForTarget' )
			->with( $this->equalTo( $title ) )
			->will( $this->returnValue( 'vi' ) );

		$interlanguageLinksLookup->expects( $this->at( 2 ) )
			->method( 'findPageLanguageForTarget' )
			->with( $this->equalTo( $target ) )
			->will( $this->returnValue( 'vi' ) );

		$title->interlanguageLinksLookup = $interlanguageLinksLookup;

		$instance = new LanguageFilterCategoryViewer(
			$title,
			$this->context
		);

		$instance->addPage( $target, 'B', '' );

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
				->will( $this->returnValue( true ) );

		$interlanguageLinksLookup->expects( $this->at( 1 ) )
			->method( 'findPageLanguageForTarget' )
			->with( $this->equalTo( $title ) )
			->will( $this->returnValue( 'vi' ) );

		$interlanguageLinksLookup->expects( $this->at( 2 ) )
			->method( 'findPageLanguageForTarget' )
			->with( $this->equalTo( $target ) )
			->will( $this->returnValue( 'en' ) );

		$title->interlanguageLinksLookup = $interlanguageLinksLookup;

		$instance = new LanguageFilterCategoryViewer(
			$title,
			$this->context
		);

		$instance->addPage( $target, 'B', '' );

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
				->will( $this->returnValue( false ) );

		$title->interlanguageLinksLookup = $interlanguageLinksLookup;

		$instance = new LanguageFilterCategoryViewer(
			$title,
			$this->context
		);

		$instance->addPage( $target, 'B', '' );

		$this->assertEmpty(
			$instance->articles
		);
	}

}
