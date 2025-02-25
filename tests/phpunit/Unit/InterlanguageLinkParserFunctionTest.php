<?php

namespace SIL\Tests;

use SIL\InterlanguageLinkParserFunction;
use SMW\Tests\PHPUnitCompat;

/**
 * @covers \SIL\InterlanguageLinkParserFunction
 * @group semantic-interlanguage-links
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class InterlanguageLinkParserFunctionTest extends \PHPUnit\Framework\TestCase {

	use PHPUnitCompat;

	private $languageLinkAnnotator;
	private $siteLanguageLinksParserOutputAppender;
	private $pageContentLanguageOnTheFlyModifier;
	private $pageContentLanguageDbModifier;

	protected function setUp(): void {
		parent::setUp();

		$this->languageLinkAnnotator = $this->getMockBuilder( '\SIL\LanguageLinkAnnotator' )
			->disableOriginalConstructor()
			->getMock();

		$this->languageLinkAnnotator->expects( $this->any() )
			->method( 'canAddAnnotation' )
			->willReturn( true );

		$this->siteLanguageLinksParserOutputAppender = $this->getMockBuilder( '\SIL\SiteLanguageLinksParserOutputAppender' )
			->disableOriginalConstructor()
			->getMock();

		$this->pageContentLanguageOnTheFlyModifier = $this->getMockBuilder( '\SIL\PageContentLanguageOnTheFlyModifier' )
			->disableOriginalConstructor()
			->getMock();

		$this->pageContentLanguageDbModifier = $this->getMockBuilder( '\SIL\PageContentLanguageDbModifier' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {
		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\InterlanguageLinkParserFunction',
			new InterlanguageLinkParserFunction(
				$title,
				$this->languageLinkAnnotator,
				$this->siteLanguageLinksParserOutputAppender,
				$this->pageContentLanguageOnTheFlyModifier,
				$this->pageContentLanguageDbModifier
			)
		);
	}

	public function testTryParseThatCausesErrorMessage() {
		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new InterlanguageLinkParserFunction(
			$title,
			$this->languageLinkAnnotator,
			$this->siteLanguageLinksParserOutputAppender,
			$this->pageContentLanguageOnTheFlyModifier,
			$this->pageContentLanguageDbModifier
		);

		$instance->setInterlanguageLinksHideState( true );

		$this->assertIsString(

			$instance->parse( 'en', 'Foo' )
		);

		$instance->setInterlanguageLinksHideState( false );

		$this->assertIsString(

			$instance->parse( '%42$', 'Foo' )
		);

		$this->assertContains(
			'-error',
			$instance->parse( '', 'Foo' )
		);

		$this->assertContains(
			'-error',
			$instance->parse( 'en', '{[[:Template:Foo]]' )
		);
	}

	public function testParseToCreateErrorMessageForKnownTarget() {
		$title = \Title::newFromText( __METHOD__ );

		$this->languageLinkAnnotator->expects( $this->never() )
			->method( 'addAnnotationForInterlanguageLink' );

		$this->siteLanguageLinksParserOutputAppender->expects( $this->once() )
			->method( 'getRedirectTargetFor' )
			->with(	\Title::newFromText( 'Foo' ) )
			->willReturn( $title );

		$this->siteLanguageLinksParserOutputAppender->expects( $this->once() )
			->method( 'tryAddLanguageTargetLinksToOutput' )
			->with(
				$this->anything(),
				$title )
			->willReturn( 'Foo' );

		$instance = new InterlanguageLinkParserFunction(
			$title,
			$this->languageLinkAnnotator,
			$this->siteLanguageLinksParserOutputAppender,
			$this->pageContentLanguageOnTheFlyModifier,
			$this->pageContentLanguageDbModifier
		);

		$instance->setInterlanguageLinksHideState( false );

		$this->assertContains(
			'-error',
			$instance->parse( 'en', 'Foo' )
		);
	}

	public function testMultipleParseCalls() {
		$title = \Title::newFromText( __METHOD__ );

		$this->siteLanguageLinksParserOutputAppender->expects( $this->any() )
			->method( 'getRedirectTargetFor' )
			->willReturn( $title );

		$instance = new InterlanguageLinkParserFunction(
			$title,
			$this->languageLinkAnnotator,
			$this->siteLanguageLinksParserOutputAppender,
			$this->pageContentLanguageOnTheFlyModifier,
			$this->pageContentLanguageDbModifier
		);

		$this->assertContains(
			'div class="sil-interlanguagelink"',
			$instance->parse( 'en', 'Foo' )
		);

		$this->assertContains(
			'div class="sil-interlanguagelink"',
			$instance->parse( 'en', 'Foo' )
		);
	}

	public function testMultipleParseCallsWithDifferentLanguagesTriggersErrorMessage() {
		$title = \Title::newFromText( __METHOD__ );

		$this->languageLinkAnnotator->expects( $this->any() )
			->method( 'hasDifferentLanguageAnnotation' )
			->willReturnOnConsecutiveCalls( false, true );

		$this->siteLanguageLinksParserOutputAppender->expects( $this->any() )
			->method( 'getRedirectTargetFor' )
			->willReturn( $title );

		$instance = new InterlanguageLinkParserFunction(
			$title,
			$this->languageLinkAnnotator,
			$this->siteLanguageLinksParserOutputAppender,
			$this->pageContentLanguageOnTheFlyModifier,
			$this->pageContentLanguageDbModifier
		);

		$instance->parse( 'en', 'Foo' );

		$this->assertContains(
			'-error',
			$instance->parse( 'fr', 'Foo' )
		);
	}

	public function testParse() {
		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$this->languageLinkAnnotator->expects( $this->once() )
			->method( 'addAnnotationForInterlanguageLink' );

		$this->siteLanguageLinksParserOutputAppender->expects( $this->any() )
			->method( 'getRedirectTargetFor' )
			->willReturn( $title );

		$instance = new InterlanguageLinkParserFunction(
			$title,
			$this->languageLinkAnnotator,
			$this->siteLanguageLinksParserOutputAppender,
			$this->pageContentLanguageOnTheFlyModifier,
			$this->pageContentLanguageDbModifier
		);

		$instance->setInterlanguageLinksHideState( false );

		$this->assertContains(
			'div class="sil-interlanguagelink"',
			$instance->parse( 'en', 'Foo' )
		);
	}

	public function testValidLanguageCodeByLowerCaseComparison() {
		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$this->languageLinkAnnotator->expects( $this->once() )
			->method( 'addAnnotationForInterlanguageLink' );

		$this->siteLanguageLinksParserOutputAppender->expects( $this->any() )
			->method( 'getRedirectTargetFor' )
			->willReturn( $title );

		$instance = new InterlanguageLinkParserFunction(
			$title,
			$this->languageLinkAnnotator,
			$this->siteLanguageLinksParserOutputAppender,
			$this->pageContentLanguageOnTheFlyModifier,
			$this->pageContentLanguageDbModifier
		);

		$instance->setInterlanguageLinksHideState( false );

		$this->assertContains(
			'div class="sil-interlanguagelink"',
			$instance->parse( 'zh-Hans', 'Foo' )
		);
	}

	public function testRevisionMode() {
		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$this->siteLanguageLinksParserOutputAppender->expects( $this->any() )
			->method( 'getRedirectTargetFor' )
			->willReturn( $title );

		$instance = new InterlanguageLinkParserFunction(
			$title,
			$this->languageLinkAnnotator,
			$this->siteLanguageLinksParserOutputAppender,
			$this->pageContentLanguageOnTheFlyModifier,
			$this->pageContentLanguageDbModifier
		);

		$instance->setRevisionModeState( true );

		$this->assertEmpty(
			$instance->parse( 'en', 'Foo' )
		);
	}

	public function testAnnotationModeIsDisabled() {
		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$languageLinkAnnotator = $this->getMockBuilder( '\SIL\LanguageLinkAnnotator' )
			->disableOriginalConstructor()
			->getMock();

		$languageLinkAnnotator->expects( $this->once() )
			->method( 'canAddAnnotation' )
			->willReturn( false );

		$instance = new InterlanguageLinkParserFunction(
			$title,
			$languageLinkAnnotator,
			$this->siteLanguageLinksParserOutputAppender,
			$this->pageContentLanguageOnTheFlyModifier,
			$this->pageContentLanguageDbModifier
		);

		$this->assertEmpty(
			$instance->parse( 'en', 'Foo' )
		);
	}

	public function testAddLanguageCodeToPageContentLanguageIntermediaryCache() {
		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$languageLinkAnnotator = $this->getMockBuilder( '\SIL\LanguageLinkAnnotator' )
			->disableOriginalConstructor()
			->getMock();

		$this->pageContentLanguageOnTheFlyModifier->expects( $this->once() )
			->method( 'addToIntermediaryCache' );

		$instance = new InterlanguageLinkParserFunction(
			$title,
			$languageLinkAnnotator,
			$this->siteLanguageLinksParserOutputAppender,
			$this->pageContentLanguageOnTheFlyModifier,
			$this->pageContentLanguageDbModifier
		);

		$instance->setRevisionModeState( true );
		$instance->parse( 'en', 'Foo' );
	}

}
