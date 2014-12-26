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
class InterlanguageLinkParserAnnotationIntegrationTest extends MwDBaseUnitTestCase {

	private $pageCreator;
	private $semanticDataValidator;
	private $subject;

	protected function setUp() {
		parent::setUp();

		$this->pageCreator = UtilityFactory::getInstance()->newpageCreator();
		$this->semanticDataValidator = UtilityFactory::getInstance()->newValidatorFactory()->newSemanticDataValidator();
	}

	protected function tearDown() {

		UtilityFactory::getInstance()
			->newPageDeleter()
			->deletePage( $this->subject->getTitle() );

		parent::tearDown();
	}

	public function testUseInterlanguageLinkParserInPage() {

		$this->subject = DIWikiPage::newFromTitle( Title::newFromText( __METHOD__ ) );

		$this->pageCreator
			->createPage( $this->subject->getTitle() )
			->doEdit( '{{INTERLANGUAGELINK:en|Lorem ipsum}}' );

		$expected = array(
			'propertyCount'  => 3,
			'properties' => array(
				DIProperty::newFromUserLabel( '_SKEY' ),
				DIProperty::newFromUserLabel( SIL_PROP_REF ),
				DIProperty::newFromUserLabel( SIL_PROP_LANG )
			),
			'propertyValues' => array( 'en', 'Lorem ipsum', __METHOD__ )
		);

		$this->semanticDataValidator->assertThatPropertiesAreSet(
			$expected,
			$this->getStore()->getSemanticData( $this->subject )->findSubSemanticData( 'sil.en' )
		);
	}

}
