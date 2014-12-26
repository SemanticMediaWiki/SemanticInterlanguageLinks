<?php

namespace SIL\Tests;

use SIL\SiteLanguageLinkModifier;
use SIL\InterlanguageLink;

use SMW\DIWikiPage;
use SMWDIBlob as DIBlob;

use Title;

/**
 * @covers \SIL\SiteLanguageLinkModifier
 *
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class SiteLanguageLinkModifierTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$titleForExternalLanguageLink = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$titleToTargetLink = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\SiteLanguageLinkModifier',
			new SiteLanguageLinkModifier( $titleForExternalLanguageLink, $titleToTargetLink )
		);
	}

	public function testModifyLanguageLinkForNoTextEntry() {

		$titleForExternalLanguageLink = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$titleToTargetLink = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$languageLink = array();

		$instance = new SiteLanguageLinkModifier(
			$titleForExternalLanguageLink,
			$titleToTargetLink
		);

		$this->assertFalse(
			$instance->modifyLanguageLink( $languageLink )
		);

		$this->assertEmpty(
			$languageLink
		);
	}

	public function testModifyLanguageLinkForInvalidSilEntry() {

		$titleForExternalLanguageLink = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$titleToTargetLink = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$languageLink = array( 'text' => 'no:sil:entry');

		$instance = new SiteLanguageLinkModifier(
			$titleForExternalLanguageLink,
			$titleToTargetLink
		);

		$this->assertFalse(
			$instance->modifyLanguageLink( $languageLink )
		);

		$this->assertEquals(
			array( 'text' => 'no:sil:entry'),
			$languageLink
		);
	}

	public function testModifyLanguageLinkForValidSilEntry() {

		$titleForExternalLanguageLink = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$titleToTargetLink = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$languageLink = array( 'text' => 'sil:en:Foo' );

		$instance = new SiteLanguageLinkModifier(
			$titleForExternalLanguageLink,
			$titleToTargetLink
		);

		$this->assertTrue(
			$instance->modifyLanguageLink( $languageLink )
		);

		$this->assertContains(
			'English',
			$languageLink
		);
	}

}
