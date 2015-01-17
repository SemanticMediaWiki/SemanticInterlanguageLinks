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

	public function testCanConstruct() {

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$this->assertInstanceOf(
			'\SIL\HookRegistry',
			new HookRegistry( $store, $cache )
		);
	}

	public function testRegister() {

		$title = Title::newFromText( __METHOD__ );

		$parser = $this->getMockBuilder( '\Parser' )
			->disableOriginalConstructor()
			->getMock();

		$wikipage = $this->getMockBuilder( '\WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$wikipage->expects( $this->any() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$wgHooks = array();

		$instance = new HookRegistry( $store, $cache );
		$instance->register( $wgHooks );

		$this->assertNotEmpty(
			$wgHooks
		);

		$this->assertHookIsExcutable(
			$wgHooks,
			'ParserFirstCallInit',
			array( &$parser )
		);

		$this->assertHookIsExcutable(
			$wgHooks,
			'NewRevisionFromEditComplete',
			array( $wikipage )
		);

		$languageLink = array();

		$this->assertHookIsExcutable(
			$wgHooks,
			'SkinTemplateGetLanguageLink',
			array( &$languageLink, $title, $title )
		);

		$pageLang = '';

		$this->assertHookIsExcutable(
			$wgHooks,
			'PageContentLanguage',
			array( $title, &$pageLang )
		);

		$page = '';

		$this->assertHookIsExcutable(
			$wgHooks,
			'ArticleFromTitle',
			array( $title, &$page )
		);
	}

	public function testRegisterSemanticMediaWikiRelatedHooks() {

		$title = Title::newFromText( __METHOD__ );

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$wgHooks = array();

		$instance = new HookRegistry( $store, $cache );
		$instance->register( $wgHooks );

		$this->assertHookIsExcutable(
			$wgHooks,
			'smwInitProperties',
			array()
		);

		$this->assertHookIsExcutable(
			$wgHooks,
			'SMW::SQLStore::BeforeDeleteSubjectComplete',
			array( $store, $title )
		);

		$this->assertHookIsExcutable(
			$wgHooks,
			'SMW::SQLStore::BeforeChangeTitleComplete',
			array( $store, $title, $title, 0, 0 )
		);
	}

	public function testRegisterSearchRelatedHooks() {

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$search = $this->getMockBuilder( '\SpecialSearch' )
			->disableOriginalConstructor()
			->getMock();

		$wgHooks = array();

		$instance = new HookRegistry( $store, $cache );
		$instance->register( $wgHooks );

		$profiles = array();

		$this->assertHookIsExcutable(
			$wgHooks,
			'SpecialSearchProfiles',
			array( &$profiles )
		);

		$form = '';
		$profile = '';
		$term = '';
		$opts = array();

		$this->assertHookIsExcutable(
			$wgHooks,
			'SpecialSearchProfileForm',
			array( $search, &$form, $profile, $term, $opts )
		);

		$titleMatches = false;
		$textMatches = false;

		$this->assertHookIsExcutable(
			$wgHooks,
			'SpecialSearchResults',
			array( $search, &$titleMatches, &$textMatches )
		);
	}

	private function assertHookIsExcutable( $wgHooks, $hookName, $arguments ) {
		foreach ( $wgHooks[ $hookName ] as $hook ) {
			$this->assertInternalType(
				'boolean',
				call_user_func_array( $hook, $arguments )
			);
		}
	}

}
