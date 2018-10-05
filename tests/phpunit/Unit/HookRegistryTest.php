<?php

namespace SIL\Tests;

use SIL\HookRegistry;
use Title;

/**
 * @covers \SIL\HookRegistry
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
	private $cacheKeyProvider;

	protected function setUp() {
		parent::setUp();

		$this->store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$this->cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$this->cacheKeyProvider = $this->getMockBuilder( '\SIL\CacheKeyProvider' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SIL\HookRegistry',
			new HookRegistry( $this->store, $this->cache, $this->cacheKeyProvider )
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

		$instance = new HookRegistry( $this->store, $this->cache, $this->cacheKeyProvider );
		$instance->register();

		$this->doTestParserFirstCallInit( $instance, $parser );
		$this->doTestNewRevisionFromEditComplete( $instance );
		$this->doTestSkinTemplateGetLanguageLink( $instance );
		$this->doTestPageContentLanguage( $instance );
		$this->doTestArticleFromTitle( $instance );
		$this->doTestArticlePurge( $instance );
		$this->doTestParserAfterTidy( $instance, $parser );

		$this->doTestInitProperties( $instance );
		$this->doTestSQLStoreBeforeDeleteSubjectCompletes( $instance );
		$this->doTestSQLStoreBeforeChangeTitleComplete( $instance );

		$this->doTestSpecialSearchProfiles( $instance );
		$this->doTestSpecialSearchProfileForm( $instance );
		$this->doTestSpecialSearchResults( $instance );
		$this->doTestSpecialSearchSetupEngine( $instance );
		$this->doTestSpecialSearchPowerBox( $instance );
	}

	public function testOnBeforeConfigCompletion() {

		$config = [
			'smwgFulltextSearchPropertyExemptionList' => []
		];

		$propertyExemptionList = [
			'__sil_iwl_lang',
			'__sil_ill_lang'
		];

		HookRegistry::onBeforeConfigCompletion( $config );

		$this->assertEquals(
			[
				'smwgFulltextSearchPropertyExemptionList' => $propertyExemptionList,
			],
			$config
		);
	}

	public function doTestParserFirstCallInit( $instance, $parser ) {

		$handler = 'ParserFirstCallInit';

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $handler ),
			[ &$parser ]
		);
	}

	public function doTestNewRevisionFromEditComplete( $instance ) {

		$handler = 'NewRevisionFromEditComplete';

		$title = Title::newFromText( __METHOD__ );

		$wikipage = $this->getMockBuilder( '\WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$wikipage->expects( $this->any() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $handler ),
			[ $wikipage ]
		);
	}

	public function doTestArticlePurge( $instance ) {

		$handler = 'ArticlePurge';

		$title = Title::newFromText( __METHOD__ );

		$wikipage = $this->getMockBuilder( '\WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$wikipage->expects( $this->any() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $handler ),
			[ &$wikipage ]
		);
	}

	public function doTestSkinTemplateGetLanguageLink( $instance ) {

		$handler = 'SkinTemplateGetLanguageLink';

		$title = Title::newFromText( __METHOD__ );
		$languageLink = [];

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $handler ),
			[ &$languageLink, $title, $title ]
		);
	}

	public function doTestPageContentLanguage( $instance ) {

		$handler = 'PageContentLanguage';
		$pageLang = '';

		$title = Title::newFromText( __METHOD__ );

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $handler ),
			[ $title, &$pageLang ]
		);
	}

	public function doTestArticleFromTitle( $instance ) {

		$handler = 'ArticleFromTitle';

		$title = Title::newFromText( __METHOD__ );
		$page = '';

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $handler ),
			[ $title, &$page ]
		);
	}

	public function doTestParserAfterTidy( $instance, $parser ) {

		$handler = 'ParserAfterTidy';
		$text = '';

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $handler ),
			[ &$parser, &$text ]
		);
	}

	public function doTestInitProperties( $instance ) {

		$handler = 'SMW::Property::initProperties';

		$propertyRegistry = $this->getMockBuilder( '\SMW\PropertyRegistry' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $handler ),
			[ $propertyRegistry ]
		);
	}

	public function doTestSQLStoreBeforeDeleteSubjectCompletes( $instance ) {

		$handler = 'SMW::SQLStore::BeforeDeleteSubjectComplete';
		$title = Title::newFromText( __METHOD__ );

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $handler ),
			[ $this->store, $title ]
		);
	}

	public function doTestSQLStoreBeforeChangeTitleComplete( $instance ) {

		$handler = 'SMW::SQLStore::BeforeChangeTitleComplete';
		$title = Title::newFromText( __METHOD__ );

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $handler ),
			[ $this->store, $title, $title, 0, 0 ]
		);
	}

	public function doTestSpecialSearchProfiles( $instance ) {

		$handler = 'SpecialSearchProfiles';
		$profiles = [];

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $handler ),
			[ &$profiles ]
		);
	}

	public function doTestSpecialSearchProfileForm( $instance ) {

		$handler = 'SpecialSearchProfileForm';

		$search = $this->getMockBuilder( '\SpecialSearch' )
			->disableOriginalConstructor()
			->getMock();

		$form = '';
		$profile = '';
		$term = '';
		$opts = [];

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $handler ),
			[ $search, &$form, $profile, $term, $opts ]
		);
	}

	public function doTestSpecialSearchResults( $instance ) {

		$handler = 'SpecialSearchResults';

		$search = $this->getMockBuilder( '\SpecialSearch' )
			->disableOriginalConstructor()
			->getMock();

		$titleMatches = false;
		$textMatches = false;

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $handler ),
			[ $search, &$titleMatches, &$textMatches ]
		);
	}

	public function doTestSpecialSearchSetupEngine( $instance ) {

		$handler = 'SpecialSearchSetupEngine';

		$profile = [];

		$request = $this->getMockBuilder( '\WebRequest' )
			->disableOriginalConstructor()
			->getMock();

		$request->expects( $this->once() )
			->method( 'getVal' )
			->will( $this->returnValue( true ) );

		$context = $this->getMockBuilder( '\RequestContext' )
			->disableOriginalConstructor()
			->getMock();

		$context->expects( $this->once() )
			->method( 'getRequest' )
			->will( $this->returnValue( $request ) );

		$search = $this->getMockBuilder( '\SpecialSearch' )
			->disableOriginalConstructor()
			->getMock();

		$search->expects( $this->once() )
			->method( 'getContext' )
			->will( $this->returnValue( $context ) );

		$searchEngine = $this->getMockBuilder( '\SearchEngine' )
			->disableOriginalConstructor()
			->getMock();

		$searchEngine->expects( $this->once() )
			->method( 'setNamespaces' );

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $handler ),
			[ $search, $profile, $searchEngine ]
		);
	}

	public function doTestSpecialSearchPowerBox( $instance ) {

		$handler = 'SpecialSearchPowerBox';
		$showSections = [];

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $handler ),
			[ &$showSections, '', [] ]
		);
	}

	private function assertThatHookIsExcutable( \Closure $handler, $arguments ) {
		$this->assertInternalType(
			'boolean',
			call_user_func_array( $handler, $arguments )
		);
	}

}
