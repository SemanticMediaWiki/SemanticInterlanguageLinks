<?php

namespace SIL\Tests;

use SIL\InterlanguageLinkParserFunction;

/**
 * @covers \SIL\InterlanguageLinkParserFunction
 *
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class InterlanguageLinkParserFunctionTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$languageLinkAnnotator = $this->getMockBuilder( '\SIL\LanguageLinkAnnotator' )
			->disableOriginalConstructor()
			->getMock();

		$siteLanguageLinksGenerator = $this->getMockBuilder( '\SIL\SiteLanguageLinksGenerator' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\InterlanguageLinkParserFunction',
			new InterlanguageLinkParserFunction(
				$title,
				$languageLinkAnnotator,
				$siteLanguageLinksGenerator
			)
		);
	}

	public function testTryParseThatCausesErrorMessage() {

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$languageLinkAnnotator = $this->getMockBuilder( '\SIL\LanguageLinkAnnotator' )
			->disableOriginalConstructor()
			->getMock();

		$siteLanguageLinksGenerator = $this->getMockBuilder( '\SIL\SiteLanguageLinksGenerator' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new InterlanguageLinkParserFunction(
			$title,
			$languageLinkAnnotator,
			$siteLanguageLinksGenerator
		);

		$instance->setInterlanguageLinksState( true );

		$this->assertInternalType(
			'string',
			$instance->parse( 'en', 'Foo' )
		);

		$instance->setInterlanguageLinksState( false );

		$this->assertInternalType(
			'string',
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

		$languageLinkAnnotator = $this->getMockBuilder( '\SIL\LanguageLinkAnnotator' )
			->disableOriginalConstructor()
			->getMock();

		$languageLinkAnnotator->expects( $this->never() )
			->method( 'addAnnotationForInterlanguageLink' );

		$siteLanguageLinksGenerator = $this->getMockBuilder( '\SIL\SiteLanguageLinksGenerator' )
			->disableOriginalConstructor()
			->getMock();

		$siteLanguageLinksGenerator->expects( $this->once() )
			->method( 'getRedirectTargetFor' )
			->with(	$this->equalTo( \Title::newFromText( 'Foo' ) ) )
			->will( $this->returnValue( $title ) );

		$siteLanguageLinksGenerator->expects( $this->once() )
			->method( 'tryAddLanguageTargetLinksToOutput' )
			->with(
				$this->anything(),
				$this->equalTo( $title ) )
			->will( $this->returnValue( 'Foo' ) );

		$instance = new InterlanguageLinkParserFunction(
			$title,
			$languageLinkAnnotator,
			$siteLanguageLinksGenerator
		);

		$instance->setInterlanguageLinksState( false );

		$this->assertContains(
			'-error',
			$instance->parse( 'en', 'Foo' )
		);
	}

	public function testMultipleParseCalls() {

		$title = \Title::newFromText( __METHOD__ );

		$languageLinkAnnotator = $this->getMockBuilder( '\SIL\LanguageLinkAnnotator' )
			->disableOriginalConstructor()
			->getMock();

		$siteLanguageLinksGenerator = $this->getMockBuilder( '\SIL\SiteLanguageLinksGenerator' )
			->disableOriginalConstructor()
			->getMock();

		$siteLanguageLinksGenerator->expects( $this->any() )
			->method( 'getRedirectTargetFor' )
			->will( $this->returnValue( $title ) );

		$instance = new InterlanguageLinkParserFunction(
			$title,
			$languageLinkAnnotator,
			$siteLanguageLinksGenerator
		);

		$this->assertEmpty(
			$instance->parse( 'en', 'Foo' )
		);

		$this->assertEmpty(
			$instance->parse( 'en', 'Foo' )
		);
	}

	public function testParse() {

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$languageLinkAnnotator = $this->getMockBuilder( '\SIL\LanguageLinkAnnotator' )
			->disableOriginalConstructor()
			->getMock();

		$languageLinkAnnotator->expects( $this->once() )
			->method( 'addAnnotationForInterlanguageLink' );

		$siteLanguageLinksGenerator = $this->getMockBuilder( '\SIL\SiteLanguageLinksGenerator' )
			->disableOriginalConstructor()
			->getMock();

		$siteLanguageLinksGenerator->expects( $this->any() )
			->method( 'getRedirectTargetFor' )
			->will( $this->returnValue( $title ) );

		$instance = new InterlanguageLinkParserFunction(
			$title,
			$languageLinkAnnotator,
			$siteLanguageLinksGenerator
		);

		$instance->setInterlanguageLinksState( false );

		$this->assertEmpty(
			$instance->parse( 'en', 'Foo' )
		);
	}

}
