<?php

namespace SIL\Tests\Integration;

use MediaWiki\MediaWikiServices;

/**
 * Asserts that the declarative extension.json hook handlers are registered with
 * MediaWiki's hook container, including the colon-namespaced Semantic MediaWiki
 * hooks (which are dispatched through the same container).
 *
 * @group semantic-interlanguage-links
 * @group semantic-mediawiki-integration
 *
 * @group medium
 *
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */
class HookHandlersTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @dataProvider hookProvider
	 */
	public function testHookIsRegistered( string $hookName ): void {
		$this->assertTrue(
			MediaWikiServices::getInstance()->getHookContainer()->isRegistered( $hookName )
		);
	}

	public function hookProvider(): array {
		return [
			[ 'ParserFirstCallInit' ],
			[ 'ParserAfterTidy' ],
			[ 'ArticlePurge' ],
			[ 'RevisionFromEditComplete' ],
			[ 'ArticleFromTitle' ],
			[ 'PageContentLanguage' ],
			[ 'SkinTemplateGetLanguageLink' ],
			[ 'SMW::Property::initProperties' ],
			[ 'SMW::SQLStore::BeforeDeleteSubjectComplete' ],
			[ 'SMW::SQLStore::BeforeChangeTitleComplete' ],
			[ 'SMW::Settings::BeforeInitializationComplete' ],
		];
	}

}
