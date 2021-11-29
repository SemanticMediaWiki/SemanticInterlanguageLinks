<?php

namespace SIL\Tests\Integration;

use SMW\Tests\MwDBaseUnitTestCase;
use SMW\Tests\Utils\UtilityFactory;

use SMW\Tests\PHPUnitCompat;
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

	use PHPUnitCompat;

	private $pageCreator;
	private $semanticDataValidator;
	private $subjects = [];

	protected function setUp() : void {
		parent::setUp();

		$this->markTestSkipped(
			"This test is broken in newer MW, needs some investigation!"
		);

		$this->pageCreator = UtilityFactory::getInstance()->newPageCreator();
		$this->semanticDataValidator = UtilityFactory::getInstance()->newValidatorFactory()
																   ->newSemanticDataValidator();

		// Manipulate the interwiki prefix on-the-fly
		$GLOBALS['wgHooks']['InterwikiLoadPrefix'][] = function( $prefix, &$interwiki ) {

			if ( $prefix !== 'en' ) {
				return true;
			}

			$interwiki = [
				'iw_prefix' => 'en',
				'iw_url' => 'http://www.example.org/$1',
				'iw_api' => false,
				'iw_wikiid' => 'foo',
				'iw_local' => true,
				'iw_trans' => false,
			];

			return false;
		};
	}

	protected function tearDown() : void {

		UtilityFactory::getInstance()->newPageDeleter()->doDeletePoolOfPages( $this->subjects );
		unset( $GLOBALS['wgHooks']['InterwikiLoadPrefix'] );

		parent::tearDown();
	}

	public function testUseInterlanguageLinkParserInPage() {

		$subject = DIWikiPage::newFromText( __METHOD__ );

		$this->pageCreator
			->createPage( $subject->getTitle() )
			->doEdit( '{{INTERLANGUAGELINK:en|Lorem ipsum}}' );

		$expected = [
			'propertyCount' => 3,
			'properties' => [
				DIProperty::newFromUserLabel( '_SKEY' ),
				DIProperty::newFromUserLabel( SIL_PROP_ILL_REF ),
				DIProperty::newFromUserLabel( SIL_PROP_ILL_LANG )
			],
			'propertyValues' => [ 'en', 'Lorem ipsum', __METHOD__ ]
		];

		$this->semanticDataValidator->assertThatPropertiesAreSet(
			$expected,
			$this->getStore()->getSemanticData( $subject )->findSubSemanticData( 'ill.en' )
		);

		$this->subjects = [ $subject ];
	}

	public function testInterlanguageLinkParserToUseRedirect() {

		$subject = DIWikiPage::newFromText( __METHOD__ );

		$this->pageCreator
			->createPage( Title::newFromText( 'Sil-redirect' ) )
			->doEdit( '{{INTERLANGUAGELINK:en|Sil-redirect}} {{INTERLANGUAGELINK:fr|Sil-redirect}}' )
			->doMoveTo( Title::newFromText( 'Sil-redirect-2' ) );

		$this->pageCreator
			->createPage( Title::newFromText( __METHOD__ ) )
			->doEdit( '{{INTERLANGUAGELINK:ja|Sil-redirect}}' );

		$expected = [
			'propertyCount' => 3,
			'properties' => [
				DIProperty::newFromUserLabel( '_SKEY' ),
				DIProperty::newFromUserLabel( SIL_PROP_ILL_REF ),
				DIProperty::newFromUserLabel( SIL_PROP_ILL_LANG )
			],
			'propertyValues' => [ 'ja', 'Sil-redirect-2', __METHOD__ ]
		];

		$this->semanticDataValidator->assertThatPropertiesAreSet(
			$expected,
			$this->getStore()->getSemanticData( $subject )->findSubSemanticData( 'ill.ja' )
		);

		$this->subjects = [ $subject, 'Sil-redirect', 'Sil-redirect-2' ];
	}

	public function testUseInterwikiLanguageLinkInPage() {

		$subject = DIWikiPage::newFromTitle( Title::newFromText( __METHOD__ ) );

		$this->pageCreator
			->createPage( $subject->getTitle() )
			->doEdit( '[[en:Foo]]' );

		$expected = [
			'propertyCount' => 3,
			'properties' => [
				DIProperty::newFromUserLabel( '_SKEY' ),
				DIProperty::newFromUserLabel( SIL_PROP_IWL_REF ),
				DIProperty::newFromUserLabel( SIL_PROP_IWL_LANG )
			],
			'propertyValues' => [ 'en', 'en:Foo', __METHOD__ ]
		];

		$this->semanticDataValidator->assertThatPropertiesAreSet(
			$expected,
			$this->getStore()->getSemanticData( $subject )->findSubSemanticData( 'iwl.en' )
		);

		$this->subjects = [ $subject ];
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
			->doEdit( '{{interlanguagelink:ja|Lorem ipsum}}' );

		$this->pageCreator
			->createPage( $subject )
			->doEdit( '{{INTERLANGUAGELIST:Lorem ipsum|InterlanguageListTemplate}}' );

		$text = $this->pageCreator->getEditInfo()->getOutput()->getText();

		$this->assertContains(
			'title="InterlanguageListParserTargetEn">English</a>',
			$text
		);

		$this->assertContains(
			'title="InterlanguageListParserTargetJa">日本語</a>',
			$text
		);

		$this->subjects = [ $template, $subject, $targetEn, $targetJa ];
	}

	public function testQuerySubjectsForWildcardPageContentLanguage() {

		$subject  = Title::newFromText( 'InterlanguageLinkByAsk' );
		$targetEn = Title::newFromText( 'InterlanguageLinkParserTargetEn' );
		$targetJa = Title::newFromText( 'InterlanguageLinkParserTargetJa' );

		$this->pageCreator
			->createPage( $targetEn )
			->doEdit( '{{INTERLANGUAGELINK:en|Lorem ipsum}}' );

		$this->pageCreator
			->createPage( $targetJa )
			->doEdit( '{{INTERLANGUAGELINK:ja|Lorem ipsum}}' );

		$this->pageCreator
			->createPage( $subject )
			->doEdit( '{{#ask: [[Has interlanguage link.Page content language::+]] }}' );

		$text = $this->pageCreator->getEditInfo()->getOutput()->getText();

		$this->assertContains(
			'title="InterlanguageLinkParserTargetEn">InterlanguageLinkParserTargetEn</a>',
			$text
		);

		$this->assertContains(
			'title="InterlanguageLinkParserTargetJa">InterlanguageLinkParserTargetJa</a>',
			$text
		);

		$this->subjects = [ $subject, $targetEn, $targetJa ];
	}

	public function testQuerySubjectsForSpecificPageContentLanguage() {

		$subject  = Title::newFromText( 'InterlanguageLinkByLanguage' );
		$targetEn = Title::newFromText( 'InterlanguageLinkByLanguageParserTargetEn' );
		$targetJa = Title::newFromText( 'InterlanguageLinkByLanguageParserTargetJa' );

		$this->pageCreator
			->createPage( $targetEn )
			->doEdit( '{{INTERLANGUAGELINK:en|Lorem ipsum}}' );

		$this->pageCreator
			->createPage( $targetJa )
			->doEdit( '{{INTERLANGUAGELINK:ja|Lorem ipsum}}' );

		$this->pageCreator
			->createPage( $subject )
			->doEdit( '{{#ask: [[Has interlanguage link.Page content language::en]] }}' );

		$text = $this->pageCreator->getEditInfo()->getOutput()->getText();

		$this->assertContains(
			'title="InterlanguageLinkByLanguageParserTargetEn">'
			. 'InterlanguageLinkByLanguageParserTargetEn</a>',
			$text
		);

		$this->assertNotContains(
			'title="InterlanguageLinkByLanguageParserTargetJa">'
			. 'InterlanguageLinkByLanguageParserTargetJa</a>',
			$text
		);

		$this->subjects = [ $subject, $targetEn, $targetJa ];
	}

}
