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

	/**
	 * @dataProvider invalidLanguageLinkProvider
	 */
	public function testModifyLanguageLinkForInvalidSilEntry( $languageLink, $expected ) {

		$titleForExternalLanguageLink = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$titleToTargetLink = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new SiteLanguageLinkModifier(
			$titleForExternalLanguageLink,
			$titleToTargetLink
		);

		$this->assertFalse(
			$instance->modifyLanguageLink( $languageLink )
		);

		$this->assertEquals(
			$expected,
			$languageLink
		);
	}

	/**
	 * @dataProvider validLanguageLinkProvider
	 */
	public function testModifyLanguageLinkForValidSilEntry( $languageLink, $expected ) {

		$titleForExternalLanguageLink = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$titleToTargetLink = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new SiteLanguageLinkModifier(
			$titleForExternalLanguageLink,
			$titleToTargetLink
		);

		$this->assertTrue(
			$instance->modifyLanguageLink( $languageLink )
		);

		$this->assertContains(
			$expected,
			$languageLink
		);
	}

	public function invalidLanguageLinkProvider() {

		$provider = array();

		$provider[] = array(
			array( 'text' => 'no:sil:entry' ),
			array( 'text' => 'no:sil:entry' )
		);

		$provider[] = array(
			array( 'text' => 'Foo' ),
			array( 'text' => 'Foo' )
		);

		return $provider;
	}

	public function validLanguageLinkProvider() {

		$provider = array();

		$provider[] = array(
			array( 'text' => 'sil:en:Foo' ),
			'English'
		);

		$provider[] = array(
			array( 'text' => 'sil:en:vi:Foo' ),
			'English'
		);

		$provider[] = array(
			array( 'text' => 'sil:ja:ja:ノート:Foo' ),
			'日本語'
		);

		return $provider;
	}

}
