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

		$interlanguageLinkAnnotator = $this->getMockBuilder( '\SIL\InterlanguageLinkAnnotator' )
			->disableOriginalConstructor()
			->getMock();

		$siteLanguageLinksGenerator = $this->getMockBuilder( '\SIL\SiteLanguageLinksGenerator' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\InterlanguageLinkParserFunction',
			new InterlanguageLinkParserFunction(
				$title,
				$interlanguageLinkAnnotator,
				$siteLanguageLinksGenerator
			)
		);
	}

	public function testParseToCreateErrorMessage() {

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinkAnnotator = $this->getMockBuilder( '\SIL\InterlanguageLinkAnnotator' )
			->disableOriginalConstructor()
			->getMock();

		$siteLanguageLinksGenerator = $this->getMockBuilder( '\SIL\SiteLanguageLinksGenerator' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new InterlanguageLinkParserFunction(
			$title,
			$interlanguageLinkAnnotator,
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
			'span class="error"',
			$instance->parse( '', 'Foo' )
		);
	}

	public function testParseToCreateErrorMessageForKnownTarget() {

		$title = \Title::newFromText( __METHOD__ );

		$interlanguageLinkAnnotator = $this->getMockBuilder( '\SIL\InterlanguageLinkAnnotator' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinkAnnotator->expects( $this->never() )
			->method( 'addAnnotationFor' );

		$siteLanguageLinksGenerator = $this->getMockBuilder( '\SIL\SiteLanguageLinksGenerator' )
			->disableOriginalConstructor()
			->getMock();

		$siteLanguageLinksGenerator->expects( $this->once() )
			->method( 'checkIfTargetIsKnownForCurrentLanguage' )
			->with( $this->equalTo( $title ) )
			->will( $this->returnValue( 'Foo' ) );

		$instance = new InterlanguageLinkParserFunction(
			$title,
			$interlanguageLinkAnnotator,
			$siteLanguageLinksGenerator
		);

		$instance->setInterlanguageLinksState( false );

		$this->assertContains(
			'span class="error"',
			$instance->parse( 'en', 'Foo' )
		);
	}

	public function testReturnMessagesForMultipleParseCalls() {

		$title = \Title::newFromText( __METHOD__ );

		$interlanguageLinkAnnotator = $this->getMockBuilder( '\SIL\InterlanguageLinkAnnotator' )
			->disableOriginalConstructor()
			->getMock();

		$siteLanguageLinksGenerator = $this->getMockBuilder( '\SIL\SiteLanguageLinksGenerator' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new InterlanguageLinkParserFunction(
			$title,
			$interlanguageLinkAnnotator,
			$siteLanguageLinksGenerator
		);

		$this->assertEmpty(
			$instance->parse( 'en', 'Foo' )
		);

		$this->assertEmpty(
			$instance->parse( 'en', 'Foo' )
		);

		$this->assertContains(
			'span class="error"',
			$instance->parse( 'vi', 'Foo' )
		);

		$this->assertContains(
			'span class="error"',
			$instance->parse( 'en', 'Bar' )
		);
	}

	public function testParse() {

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinkAnnotator = $this->getMockBuilder( '\SIL\InterlanguageLinkAnnotator' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinkAnnotator->expects( $this->once() )
			->method( 'addAnnotationFor' );

		$siteLanguageLinksGenerator = $this->getMockBuilder( '\SIL\SiteLanguageLinksGenerator' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new InterlanguageLinkParserFunction(
			$title,
			$interlanguageLinkAnnotator,
			$siteLanguageLinksGenerator
		);

		$instance->setInterlanguageLinksState( false );

		$this->assertEmpty(
			$instance->parse( 'en', 'Foo' )
		);
	}

}
