<?php

namespace SIL\Tests\Integration;

use SMW\Tests\MwDBaseUnitTestCase;
use SMW\Tests\Utils\UtilityFactory;
use SMW\Tests\PHPUnitCompat;
use Title;
use Article;
use RequestContext;

/**
 * @group semantic-interlanguage-links
 * @group semantic-mediawiki-integration
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class LanguageFilterCategoryViewerIntegrationTest extends MwDBaseUnitTestCase {

	use PHPUnitCompat;

	private $pageCreator;
	private $subjects = [];

	protected function setUp() : void {
		parent::setUp();

		$this->pageCreator = UtilityFactory::getInstance()->newpageCreator();
	}

	protected function tearDown() : void {

		UtilityFactory::getInstance()
			->newPageDeleter()
			->doDeletePoolOfPages( $this->subjects );

		parent::tearDown();
	}

	public function testCategoryViewerToDisplayAll() {

		$category = Title::newFromText( 'CategoryViewerToDisplayAll', NS_CATEGORY );
		$targetEn = Title::newFromText( 'CategoryViewerToDisplayAllTargetEn' );
		$targetJa = Title::newFromText( 'CategoryViewerToDisplayAllTargetJa' );

		$this->pageCreator
			->createPage( $targetEn )
			->doEdit( '{{INTERLANGUAGELINK:en|Lorem ipsum}} [[Category:CategoryViewerToDisplayAll]]' );

		$this->pageCreator
			->createPage( $targetJa )
			->doEdit( '{{INTERLANGUAGELINK:ja|Lorem ipsum}} [[Category:CategoryViewerToDisplayAll]]' );

		$this->pageCreator
			->createPage( $category );

		$context = new RequestContext();
		$context->setTitle( $category );

		$instance = Article::newFromTitle( $category, $context );
		$instance->view();

		$text = $instance->getContext()->getOutput()->getHTML();

		$this->assertContains(
			'title="CategoryViewerToDisplayAllTargetEn">CategoryViewerToDisplayAllTargetEn</a>',
			$text
		);

		$this->assertContains(
			'title="CategoryViewerToDisplayAllTargetJa">CategoryViewerToDisplayAllTargetJa</a>',
			$text
		);

		$this->subjects = [ $category, $targetEn, $targetJa ];
	}

	public function testCategoryViewerToDisplayByLanguageOnly() {

		$category = Title::newFromText( 'CategoryViewerByLanguage', NS_CATEGORY );
		$targetEn = Title::newFromText( 'CategoryViewerByLanguageTargetEn' );
		$targetJa = Title::newFromText( 'CategoryViewerByLanguageTargetJa' );

		$this->pageCreator
			->createPage( $targetEn )
			->doEdit( '{{INTERLANGUAGELINK:en|CategoryViewerByLanguage}} [[Category:CategoryViewerByLanguage]]' );

		$this->pageCreator
			->createPage( $targetJa )
			->doEdit( '{{INTERLANGUAGELINK:ja|CategoryViewerByLanguage}} [[Category:CategoryViewerByLanguage]]' );

		$this->pageCreator
			->createPage( $category )
			->doEdit( '{{INTERLANGUAGELINK:en|Category:CategoryViewerByLanguage}}' );

		$context = new RequestContext();
		$context->setTitle( $category );

		$instance = Article::newFromTitle( $category, $context );
		$instance->view();

		$text = $instance->getContext()->getOutput()->getHTML();

		$this->assertContains(
			'title="CategoryViewerByLanguageTargetEn">CategoryViewerByLanguageTargetEn</a>',
			$text
		);

		$this->subjects = [ $category, $targetEn, $targetJa ];
	}

}
