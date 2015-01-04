<?php

namespace SIL\Tests;

use SIL\PageContentLanguageModifier;
use SMWDIBlob as DIBlob;

/**
 * @covers \SIL\PageContentLanguageModifier
 *
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class PageContentLanguageModifierTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\PageContentLanguageModifier',
			new PageContentLanguageModifier( $interlanguageLinksLookup, $title )
		);
	}

	public function testModifyLanguageForValidLanguage() {

		$pageLanguage = '';

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'findPageLanguageForTarget' )
			->will( $this->returnValue(  'ja' ) );

		$instance = new PageContentLanguageModifier( $interlanguageLinksLookup, $title );

		$this->assertTrue(
			$instance->modifyLanguage( $pageLanguage )
		);

		$this->assertEquals(
			'ja',
			$pageLanguage
		);
	}

	/**
	 * @dataProvider invalidLanguageCodeProvider
	 */
	public function testModifyLanguageForInvalidLanguage( $invalidLanguageCode ) {

		$pageLanguage = '';

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup = $this->getMockBuilder( '\SIL\InterlanguageLinksLookup' )
			->disableOriginalConstructor()
			->getMock();

		$interlanguageLinksLookup->expects( $this->once() )
			->method( 'findPageLanguageForTarget' )
			->will( $this->returnValue( $invalidLanguageCode ) );

		$instance = new PageContentLanguageModifier( $interlanguageLinksLookup, $title );

		$this->assertTrue(
			$instance->modifyLanguage( $pageLanguage )
		);

		$this->assertEmpty(
			$pageLanguage
		);
	}

	public function invalidLanguageCodeProvider() {

		$provider = array(
			array( null ),
			array( '' ),
			array( false )
		);

		return $provider;
	}

}
