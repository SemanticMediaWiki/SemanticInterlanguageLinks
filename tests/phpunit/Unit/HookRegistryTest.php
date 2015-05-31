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

		$instance = new HookRegistry( $this->store, $this->cache, 'foo' );
		$instance->register();

		$this->doTestParserFirstCallInit( $instance, $parser );
		$this->doTestNewRevisionFromEditComplete( $instance, $title );
		$this->doTestSkinTemplateGetLanguageLink( $instance, $title );
		$this->doTestPageContentLanguage( $instance, $title );
		$this->doTestArticleFromTitle( $instance, $title );
		$this->doTestParserAfterTidy( $instance, $parser );

		$this->doTestInitProperties( $instance );
		$this->doTestSQLStoreBeforeDeleteSubjectCompletes( $instance, $this->store, $title );
		$this->doTestSQLStoreBeforeChangeTitleComplete( $instance, $this->store, $title );

		$this->doTestSpecialSearchProfiles( $instance );
		$this->doTestSpecialSearchProfileForm( $instance );
		$this->doTestSpecialSearchResults( $instance );
		$this->doTestSpecialSearchPowerBox( $instance );
	}

	public function doTestParserFirstCallInit( $instance, $parser ) {

		$this->assertTrue(
			$instance->isRegistered( 'ParserFirstCallInit' )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlersFor( 'ParserFirstCallInit' ),
			array( &$parser )
		);
	}

	public function doTestNewRevisionFromEditComplete( $instance, $title ) {

		$wikipage = $this->getMockBuilder( '\WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$wikipage->expects( $this->any() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$this->assertTrue(
			$instance->isRegistered( 'NewRevisionFromEditComplete' )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlersFor( 'NewRevisionFromEditComplete' ),
			array( $wikipage )
		);
	}

	public function doTestSkinTemplateGetLanguageLink( $instance, $title ) {

		$languageLink = array();

		$this->assertTrue(
			$instance->isRegistered( 'SkinTemplateGetLanguageLink' )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlersFor( 'SkinTemplateGetLanguageLink' ),
			array( &$languageLink, $title, $title )
		);
	}

	public function doTestPageContentLanguage( $instance, $title ) {

		$pageLang = '';

		$this->assertTrue(
			$instance->isRegistered( 'PageContentLanguage' )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlersFor( 'PageContentLanguage' ),
			array( $title, &$pageLang )
		);
	}

	public function doTestArticleFromTitle( $instance, $title ) {

		$page = '';

		$this->assertTrue(
			$instance->isRegistered( 'ArticleFromTitle' )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlersFor( 'ArticleFromTitle' ),
			array( $title, &$page )
		);
	}

	public function doTestParserAfterTidy( $instance, $parser ) {

		$text = '';

		$this->assertTrue(
			$instance->isRegistered( 'ParserAfterTidy' )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlersFor( 'ParserAfterTidy' ),
			array( &$parser, &$text )
		);
	}

	public function doTestInitProperties( $instance ) {

		$this->assertTrue(
			$instance->isRegistered( 'SMW::Property::initProperties' )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlersFor( 'SMW::Property::initProperties' ),
			array()
		);
	}

	public function doTestSQLStoreBeforeDeleteSubjectCompletes( $instance, $store, $title ) {

		$this->assertTrue(
			$instance->isRegistered( 'SMW::SQLStore::BeforeDeleteSubjectComplete' )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlersFor( 'SMW::SQLStore::BeforeDeleteSubjectComplete' ),
			array( $store, $title )
		);
	}

	public function doTestSQLStoreBeforeChangeTitleComplete( $instance, $store, $title ) {

		$this->assertTrue(
			$instance->isRegistered( 'SMW::SQLStore::BeforeChangeTitleComplete' )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlersFor( 'SMW::SQLStore::BeforeChangeTitleComplete' ),
			array( $store, $title, $title, 0, 0 )
		);
	}

	public function doTestSpecialSearchProfiles( $instance ) {

		$profiles = array();

		$this->assertTrue(
			$instance->isRegistered( 'SpecialSearchProfiles' )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlersFor( 'SpecialSearchProfiles' ),
			array( &$profiles )
		);
	}

	public function doTestSpecialSearchProfileForm( $instance ) {

		$search = $this->getMockBuilder( '\SpecialSearch' )
			->disableOriginalConstructor()
			->getMock();

		$form = '';
		$profile = '';
		$term = '';
		$opts = array();

		$this->assertTrue(
			$instance->isRegistered( 'SpecialSearchProfileForm' )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlersFor( 'SpecialSearchProfileForm' ),
			array( $search, &$form, $profile, $term, $opts )
		);
	}

	public function doTestSpecialSearchResults( $instance ) {

		$search = $this->getMockBuilder( '\SpecialSearch' )
			->disableOriginalConstructor()
			->getMock();

		$titleMatches = false;
		$textMatches = false;

		$this->assertTrue(
			$instance->isRegistered( 'SpecialSearchResults' )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlersFor( 'SpecialSearchResults' ),
			array( $search, &$titleMatches, &$textMatches )
		);
	}

	public function doTestSpecialSearchPowerBox( $instance ) {

		$showSections = array();

		$this->assertTrue(
			$instance->isRegistered( 'SpecialSearchPowerBox' )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlersFor( 'SpecialSearchPowerBox' ),
			array( &$showSections, '', array() )
		);
	}

	private function assertThatHookIsExcutable( \Closure $handler, $arguments ) {
		$this->assertInternalType(
			'boolean',
			call_user_func_array( $handler, $arguments )
		);
	}

}
