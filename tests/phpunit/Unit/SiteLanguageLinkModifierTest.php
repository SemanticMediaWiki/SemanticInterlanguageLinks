<?php

namespace SIL\Tests;

use MediaWiki\Title\Title;
use SIL\SiteLanguageLinkModifier;

/**
 * @covers \SIL\SiteLanguageLinkModifier
 *
 * @group semantic-interlanguage-links
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class SiteLanguageLinkModifierTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$titleForExternalLanguageLink = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$titleToTargetLink = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\SiteLanguageLinkModifier',
			new SiteLanguageLinkModifier( $titleForExternalLanguageLink, $titleToTargetLink )
		);
	}

	public function testModifyLanguageLinkForNoTextEntry() {
		$titleForExternalLanguageLink = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$titleToTargetLink = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$languageLink = [];

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
		$titleForExternalLanguageLink = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$titleToTargetLink = $this->getMockBuilder( Title::class )
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
		$titleForExternalLanguageLink = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$titleToTargetLink = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$instance = new SiteLanguageLinkModifier(
			$titleForExternalLanguageLink,
			$titleToTargetLink
		);

		$this->assertTrue(
			$instance->modifyLanguageLink( $languageLink )
		);

		$this->assertStringContainsString(
			$expected,
			implode( ' ', $languageLink )
		);
	}

	public function invalidLanguageLinkProvider() {
		$provider = [];

		$provider[] = [
			[ 'text' => 'no:sil:entry' ],
			[ 'text' => 'no:sil:entry' ]
		];

		$provider[] = [
			[ 'text' => 'Foo' ],
			[ 'text' => 'Foo' ]
		];

		return $provider;
	}

	public function validLanguageLinkProvider() {
		$provider = [];

		$provider[] = [
			[ 'text' => 'sil:en:Foo' ],
			'English'
		];

		$provider[] = [
			[ 'text' => 'sil:en:vi:Foo' ],
			'English'
		];

		$provider[] = [
			[ 'text' => 'sil:ja:ja:ノート:Foo' ],
			'日本語'
		];

		return $provider;
	}

}
