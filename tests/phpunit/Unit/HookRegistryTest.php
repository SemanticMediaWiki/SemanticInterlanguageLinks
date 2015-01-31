<?php

namespace SIL\Tests;

use SIL\HookRegistry;

use Title;

/**
 * @covers \SIL\HookRegistry
 *
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class HookRegistryTest extends \PHPUnit_Framework_TestCase {

	private $cache;
	private $store;

	protected function setUp() {
		parent::setUp();

		$this->store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$this->cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SIL\HookRegistry',
			new HookRegistry( $this->store, $this->cache, 'foo' )
		);
	}

	public function testRegister() {

		$title = Title::newFromText( __METHOD__ );

		$parserOutput = $this->getMockBuilder( '\ParserOutput' )
			->disableOriginalConstructor()
			->getMock();

		$parser = $this->getMockBuilder( '\Parser' )
			->disableOriginalConstructor()
			->getMock();

		$parser->expects( $this->any() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$parser->expects( $this->any() )
			->method( 'getOutput' )
			->will( $this->returnValue( $parserOutput ) );

		$wgHooks = array();

		$instance = new HookRegistry( $this->store, $this->cache, 'foo' );
		$instance->register( $wgHooks );

		$this->assertNotEmpty(
			$wgHooks
		);

		$this->doTestParserFirstCallInit( $wgHooks, $parser );
		$this->doTestNewRevisionFromEditComplete( $wgHooks, $title );
		$this->doTestSkinTemplateGetLanguageLink( $wgHooks, $title );
		$this->doTestPageContentLanguage( $wgHooks, $title );
		$this->doTestArticleFromTitle( $wgHooks, $title );
		$this->doTestParserAfterTidy( $wgHooks, $parser );

		$this->doTestInitProperties( $wgHooks );
		$this->doTestSQLStoreBeforeDeleteSubjectCompletes( $wgHooks, $this->store, $title );
		$this->doTestSQLStoreBeforeChangeTitleComplete( $wgHooks, $this->store, $title );

		$this->doTestSpecialSearchProfiles( $wgHooks );
		$this->doTestSpecialSearchProfileForm( $wgHooks );
		$this->doTestSpecialSearchResults( $wgHooks );
		$this->doTestSpecialSearchPowerBox( $wgHooks );
	}

	public function doTestParserFirstCallInit( $wgHooks, $parser ) {

		$this->assertThatHookIsExcutable(
			$wgHooks,
			'ParserFirstCallInit',
			array( &$parser )
		);
	}

	public function doTestNewRevisionFromEditComplete( $wgHooks, $title ) {

		$wikipage = $this->getMockBuilder( '\WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$wikipage->expects( $this->any() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$this->assertThatHookIsExcutable(
			$wgHooks,
			'NewRevisionFromEditComplete',
			array( $wikipage )
		);
	}

	public function doTestSkinTemplateGetLanguageLink( $wgHooks, $title ) {

		$languageLink = array();

		$this->assertThatHookIsExcutable(
			$wgHooks,
			'SkinTemplateGetLanguageLink',
			array( &$languageLink, $title, $title )
		);
	}

	public function doTestPageContentLanguage( $wgHooks, $title ) {

		$pageLang = '';

		$this->assertThatHookIsExcutable(
			$wgHooks,
			'PageContentLanguage',
			array( $title, &$pageLang )
		);
	}

	public function doTestArticleFromTitle( $wgHooks, $title ) {

		$page = '';

		$this->assertThatHookIsExcutable(
			$wgHooks,
			'ArticleFromTitle',
			array( $title, &$page )
		);
	}

	public function doTestParserAfterTidy( $wgHooks, $parser ) {

		$text = '';

		$this->assertThatHookIsExcutable(
			$wgHooks,
			'ParserAfterTidy',
			array( &$parser, &$text )
		);
	}

	public function doTestInitProperties( $wgHooks ) {

		$this->assertThatHookIsExcutable(
			$wgHooks,
			'smwInitProperties',
			array()
		);
	}

	public function doTestSQLStoreBeforeDeleteSubjectCompletes( $wgHooks, $store, $title ) {

		$this->assertThatHookIsExcutable(
			$wgHooks,
			'SMW::SQLStore::BeforeDeleteSubjectComplete',
			array( $this->store, $title )
		);
	}

	public function doTestSQLStoreBeforeChangeTitleComplete( $wgHooks, $store, $title ) {

		$this->assertThatHookIsExcutable(
			$wgHooks,
			'SMW::SQLStore::BeforeChangeTitleComplete',
			array( $store, $title, $title, 0, 0 )
		);
	}

	public function doTestSpecialSearchProfiles( $wgHooks ) {

		$profiles = array();

		$this->assertThatHookIsExcutable(
			$wgHooks,
			'SpecialSearchProfiles',
			array( &$profiles )
		);
	}

	public function doTestSpecialSearchProfileForm( $wgHooks ) {

		$search = $this->getMockBuilder( '\SpecialSearch' )
			->disableOriginalConstructor()
			->getMock();

		$form = '';
		$profile = '';
		$term = '';
		$opts = array();

		$this->assertThatHookIsExcutable(
			$wgHooks,
			'SpecialSearchProfileForm',
			array( $search, &$form, $profile, $term, $opts )
		);
	}

	public function doTestSpecialSearchResults( $wgHooks ) {

		$search = $this->getMockBuilder( '\SpecialSearch' )
			->disableOriginalConstructor()
			->getMock();

		$titleMatches = false;
		$textMatches = false;

		$this->assertThatHookIsExcutable(
			$wgHooks,
			'SpecialSearchResults',
			array( $search, &$titleMatches, &$textMatches )
		);
	}

	public function doTestSpecialSearchPowerBox( $wgHooks ) {

		$showSections = array();

		$this->assertThatHookIsExcutable(
			$wgHooks,
			'SpecialSearchPowerBox',
			array( &$showSections, '', array() )
		);
	}

	private function assertThatHookIsExcutable( $wgHooks, $hookName, $arguments ) {
		foreach ( $wgHooks[ $hookName ] as $hook ) {
			$this->assertInternalType(
				'boolean',
				call_user_func_array( $hook, $arguments )
			);
		}
	}

}
