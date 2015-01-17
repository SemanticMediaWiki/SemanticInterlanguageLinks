<?php

namespace SIL\Tests\Integration;

use SMW\Tests\MwDBaseUnitTestCase;
use SMW\Tests\Utils\UtilityFactory;

use SMW\DIWikiPage;
use SMW\DIProperty;

use Title;

/**
 * @group semantic-interlanguage-links
 * @group semantic-mediawiki-integration
 *
 * @group mediawiki-database
 * @group medium
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ParserFunctionIntegrationTest extends MwDBaseUnitTestCase {

	private $pageCreator;
	private $semanticDataValidator;
	private $subjects = array();

	protected function setUp() {
		parent::setUp();

		$this->pageCreator = UtilityFactory::getInstance()->newpageCreator();
		$this->semanticDataValidator = UtilityFactory::getInstance()->newValidatorFactory()->newSemanticDataValidator();
	}

	protected function tearDown() {

		UtilityFactory::getInstance()
			->newPageDeleter()
			->doDeletePoolOfPages( $this->subjects );

		parent::tearDown();
	}

	public function testUseInterlanguageLinkParserInPage() {

		$subject = DIWikiPage::newFromTitle( Title::newFromText( __METHOD__ ) );

		$this->pageCreator
			->createPage( $subject->getTitle() )
			->doEdit( '{{INTERLANGUAGELINK:en|Lorem ipsum}}' );

		$expected = array(
			'propertyCount' => 3,
			'properties' => array(
				DIProperty::newFromUserLabel( '_SKEY' ),
				DIProperty::newFromUserLabel( SIL_PROP_REF ),
				DIProperty::newFromUserLabel( SIL_PROP_LANG )
			),
			'propertyValues' => array( 'en', 'Lorem ipsum', __METHOD__ )
		);

		$this->semanticDataValidator->assertThatPropertiesAreSet(
			$expected,
			$this->getStore()->getSemanticData( $subject )->findSubSemanticData( 'sil.en' )
		);

		$this->subjects = array( $subject );
	}

	public function testUseInterlanguageListParserForTemplateInclusion() {

		$subject  = Title::newFromText( 'InterlanguageList' );
		$targetEn = Title::newFromText( 'InterlanguageListParserTargetEn' );
		$targetJa = Title::newFromText( 'InterlanguageListParserTargetJa' );
		$template = Title::newFromText( 'InterlanguageListTemplate', NS_TEMPLATE );

		$this->pageCreator
			->createPage( $template )
			->doEdit( '<includeonly>[[{{{target-link}}}|{{{lang-name}}}]]</includeonly>' );

		$this->pageCreator
			->createPage( $targetEn )
			->doEdit( '{{INTERLANGUAGELINK:en|Lorem ipsum}}' );

		$this->pageCreator
			->createPage( $targetJa )
			->doEdit( '{{INTERLANGUAGELINK:ja|Lorem ipsum}}' );

		$this->pageCreator
			->createPage( $subject )
			->doEdit( '{{INTERLANGUAGELIST:Lorem ipsum|InterlanguageListTemplate}}' );

		$text = $this->pageCreator->getEditInfo()->output->getText();

		$this->assertContains(
			'title="InterlanguageListParserTargetEn">English</a>',
			$text
		);

		$this->assertContains(
			'title="InterlanguageListParserTargetJa">日本語</a>',
			$text
		);

		$this->subjects = array( $template, $subject, $targetEn, $targetJa );
	}

}
