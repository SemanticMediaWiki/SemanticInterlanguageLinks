<?php

namespace SIL\Tests\Search;

use SIL\Search\SearchResultModifier;
use SMW\Tests\PHPUnitCompat;

/**
 * @covers \SIL\Search\SearchResultModifier
 *
 * @group semantic-interlanguage-links
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class SearchResultModifierTest extends \PHPUnit\Framework\TestCase {

	use PHPUnitCompat;

	public function testCanConstruct() {
		$languageResultMatchFinder = $this->getMockBuilder( '\SIL\Search\LanguageResultMatchFinder' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\Search\SearchResultModifier',
			new SearchResultModifier( $languageResultMatchFinder )
		);
	}

	public function testAddSearchProfile() {
		$languageResultMatchFinder = $this->getMockBuilder( '\SIL\Search\LanguageResultMatchFinder' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new SearchResultModifier( $languageResultMatchFinder );

		$profiles = [];

		$instance->addSearchProfile( $profiles );

		$this->assertArrayHasKey(
			'sil',
			$profiles
		);

		$this->assertIsArray(

			$profiles['sil']['namespaces']
		);
	}

	public function testAddSearchFormForSILProfile() {
		$languageResultMatchFinder = $this->getMockBuilder( '\SIL\Search\LanguageResultMatchFinder' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new SearchResultModifier( $languageResultMatchFinder );

		$request = $this->getMockBuilder( '\WebRequest' )
			->disableOriginalConstructor()
			->getMock();

		$request->expects( $this->once() )
			->method( 'getVal' )
			->with( 'languagefilter' )
			->willReturn( 'vi' );

		$context = $this->getMockBuilder( '\IContextSource' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$context->expects( $this->once() )
			->method( 'getRequest' )
			->willReturn( $request );

		$specialSearch = $this->getMockBuilder( '\SpecialSearch' )
			->disableOriginalConstructor()
			->getMock();

		$specialSearch->expects( $this->once() )
			->method( 'getContext' )
			->willReturn( $context );

		$specialSearch->expects( $this->once() )
			->method( 'setExtraParam' )
			->with(
				'languagefilter',
				'vi' );

		$form = '';
		$opts = [ 'Foo' => 'Bar' ];

		$this->assertFalse(
			$instance->addSearchProfileForm( $specialSearch, 'sil', $form, $opts )
		);

		$this->assertContains(
			'languagefilter',
			$form
		);

		$this->assertContains(
			'Foo',
			$form
		);
	}

	public function testNoSearchFormForNonSILProfile() {
		$languageResultMatchFinder = $this->getMockBuilder( '\SIL\Search\LanguageResultMatchFinder' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new SearchResultModifier( $languageResultMatchFinder );

		$specialSearch = $this->getMockBuilder( '\SpecialSearch' )
			->disableOriginalConstructor()
			->getMock();

		$form = '';
		$opts = [];

		$this->assertTrue(
			$instance->addSearchProfileForm( $specialSearch, 'foo', $form, $opts )
		);
	}

	public function testAddLanguageFilterToPowerBox() {
		$languageResultMatchFinder = $this->getMockBuilder( '\SIL\Search\LanguageResultMatchFinder' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new SearchResultModifier( $languageResultMatchFinder );

		$request = $this->getMockBuilder( '\WebRequest' )
			->disableOriginalConstructor()
			->getMock();

		$request->expects( $this->once() )
			->method( 'getVal' )
			->with( 'languagefilter' )
			->willReturn( 'en' );

		$this->assertTrue(
			$instance->addLanguageFilterToPowerBox( $request, $showSections )
		);

		$this->assertArrayHasKey(
			'sil-languagefilter',
			$showSections
		);
	}

	public function testNoPostFilteringForNonSILProfile() {
		$languageResultMatchFinder = $this->getMockBuilder( '\SIL\Search\LanguageResultMatchFinder' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new SearchResultModifier( $languageResultMatchFinder );

		$request = $this->getMockBuilder( '\WebRequest' )
			->disableOriginalConstructor()
			->getMock();

		$request->expects( $this->once() )
			->method( 'getVal' )
			->with( 'profile' )
			->willReturn( 'foo' );

		$titleMatches = false;
		$textMatches = false;

		$this->assertFalse(
			$instance->applyLanguageFilterToResultMatches( $request, $titleMatches, $textMatches )
		);
	}

	/**
	 * @dataProvider validProfileProvider
	 */
	public function testTryPostFilteringByValidProfileForValidLanguageCode( $profile ) {
		$titleMatches = $this->getMockBuilder( '\SearchResultSet' )
			->disableOriginalConstructor()
			->getMock();

		$textMatches = $this->getMockBuilder( '\SearchResultSet' )
			->disableOriginalConstructor()
			->getMock();

		$languageResultMatchFinder = $this->getMockBuilder( '\SIL\Search\LanguageResultMatchFinder' )
			->disableOriginalConstructor()
			->getMock();

		$languageResultMatchFinder->expects( $this->at( 0 ) )
			->method( 'matchResultsToLanguage' )
			->with(
				$titleMatches,
				'zh-Hans' );

		$languageResultMatchFinder->expects( $this->at( 1 ) )
			->method( 'matchResultsToLanguage' )
			->with(
				$textMatches,
				'zh-Hans' );

		$instance = new SearchResultModifier( $languageResultMatchFinder );

		$request = $this->getMockBuilder( '\WebRequest' )
			->disableOriginalConstructor()
			->getMock();

		$request->expects( $this->at( 0 ) )
			->method( 'getVal' )
			->with( 'profile' )
			->willReturn( $profile );

		$request->expects( $this->at( 1 ) )
			->method( 'getVal' )
			->with( 'languagefilter' )
			->willReturn( 'zh-hans' );

		$this->assertTrue(
			$instance->applyLanguageFilterToResultMatches( $request, $titleMatches, $textMatches )
		);
	}

	public function testCreateHtmlLanguageFilterSelector() {
		$languageResultMatchFinder = $this->getMockBuilder( '\SIL\Search\LanguageResultMatchFinder' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new SearchResultModifier( $languageResultMatchFinder );

		$this->assertIsString(

			$instance->createHtmlLanguageFilterSelector( 'en' )
		);
	}

	/**
	 * @dataProvider invalidLanguageCodeProvider
	 */
	public function testTryPostFilteringForSILProfileByInvalidLanguageCode( $invalidLanguageCode ) {
		$languageResultMatchFinder = $this->getMockBuilder( '\SIL\Search\LanguageResultMatchFinder' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new SearchResultModifier( $languageResultMatchFinder );

		$request = $this->getMockBuilder( '\WebRequest' )
			->disableOriginalConstructor()
			->getMock();

		$request->expects( $this->at( 0 ) )
			->method( 'getVal' )
			->with( 'profile' )
			->willReturn( 'sil' );

		$request->expects( $this->at( 1 ) )
			->method( 'getVal' )
			->with( 'languagefilter' )
			->willReturn( $invalidLanguageCode );

		$titleMatches = false;
		$textMatches = false;

		$this->assertFalse(
			$instance->applyLanguageFilterToResultMatches( $request, $titleMatches, $textMatches )
		);
	}

	public function invalidLanguageCodeProvider() {
		$provider = [
			[ null ],
			[ '' ],
			[ false ],
			[ '-' ]
		];

		return $provider;
	}

	public function validProfileProvider() {
		$provider = [
			[ 'sil' ],
			[ 'advanced' ]
		];

		return $provider;
	}

}
