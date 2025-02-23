<?php

namespace SIL;

use MediaWiki\MediaWikiServices;
use Onoi\Cache\Cache;
use SIL\Category\LanguageFilterCategoryPage;
use SMW\InMemoryPoolCache;
use SMW\Services\ServicesFactory as ApplicationFactory;
use SMW\Store;

/**
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class HookRegistry {

	/**
	 * @var array
	 */
	private $handlers = [];

	/**
	 * @since 1.0
	 *
	 * @param Store $store
	 * @param Cache $cache
	 * @param CacheKeyProvider $cacheKeyProvider
	 */
	public function __construct( Store $store, Cache $cache, CacheKeyProvider $cacheKeyProvider ) {
		$this->addCallbackHandlers( $store, $cache, $cacheKeyProvider );
	}

	/**
	 * @since  1.1
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isRegistered( $name ) {
		return MediaWikiServices::getInstance()->getHookContainer()->isRegistered( $name );
	}

	/**
	 * @since  1.1
	 *
	 * @param string $name
	 *
	 * @return callable|false
	 */
	public function getHandlerFor( $name ) {
		return isset( $this->handlers[$name] ) ? $this->handlers[$name] : false;
	}

	/**
	 * @since  1.0
	 */
	public function register() {
		$hooks = MediaWikiServices::getInstance()->getHookContainer();
		foreach ( $this->handlers as $name => $callback ) {
			$hooks->register( $name, $callback );
		}
	}

	/**
	 * @since  1.0
	 *
	 * @param array &$config
	 */
	public static function onBeforeConfigCompletion( &$config ) {
		if ( !isset( $config['smwgFulltextSearchPropertyExemptionList'] ) ) {
			return;
		}

		// Exclude those properties from indexing
		$config['smwgFulltextSearchPropertyExemptionList'] = array_merge(
			$config['smwgFulltextSearchPropertyExemptionList'],
			[ PropertyRegistry::SIL_IWL_LANG, PropertyRegistry::SIL_ILL_LANG ]
		);
	}

	private function addCallbackHandlers( $store, $cache, $cacheKeyProvider ) {
		$languageTargetLinksCache = new LanguageTargetLinksCache(
			$cache,
			$cacheKeyProvider
		);

		$interlanguageLinksLookup = new InterlanguageLinksLookup(
			$languageTargetLinksCache
		);

		$interlanguageLinksLookup->setStore( $store );

		/**
		 * @see https://github.com/SemanticMediaWiki/SemanticMediaWiki/blob/master/docs/technical/hooks/hook.property.initproperties.md
		 */
		$this->handlers['SMW::Property::initProperties'] = static function ( $baseRegistry ) {
			$propertyRegistry = new PropertyRegistry();

			$propertyRegistry->register(
				$baseRegistry
			);

			return true;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ArticleFromTitle
		 */
		$this->handlers['ArticleFromTitle'] = static function ( $title, &$page ) use( $interlanguageLinksLookup ) {
			$languageFilterCategoryPage = new LanguageFilterCategoryPage( $title );
			$languageFilterCategoryPage->isCategoryFilterByLanguage( $GLOBALS['silgEnabledCategoryFilterByLanguage'] );
			$languageFilterCategoryPage->modifyCategoryView( $page, $interlanguageLinksLookup );

			return true;
		};

		$this->registerInterlanguageParserHooks( $interlanguageLinksLookup );
	}

	private function registerInterlanguageParserHooks( InterlanguageLinksLookup $interlanguageLinksLookup ) {
		$pageContentLanguageOnTheFlyModifier = new PageContentLanguageOnTheFlyModifier(
			$interlanguageLinksLookup,
			InMemoryPoolCache::getInstance()->getPoolCacheFor( PageContentLanguageOnTheFlyModifier::POOLCACHE_ID )
		);

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
		 */
		$this->handlers['ParserFirstCallInit'] = static function ( &$parser ) use( $interlanguageLinksLookup, $pageContentLanguageOnTheFlyModifier ) {
			$parserFunctionFactory = new ParserFunctionFactory();

			[ $name, $definition, $flag ] = $parserFunctionFactory->newInterlanguageLinkParserFunctionDefinition(
				$interlanguageLinksLookup,
				$pageContentLanguageOnTheFlyModifier
			);

			$parser->setFunctionHook( $name, $definition, $flag );

			[ $name, $definition, $flag ] = $parserFunctionFactory->newInterlanguageListParserFunctionDefinition(
				$interlanguageLinksLookup
			);

			$parser->setFunctionHook( $name, $definition, $flag );

			[ $name, $definition, $flag ] = $parserFunctionFactory->newAnnotatedLanguageParserFunctionDefinition(
				$interlanguageLinksLookup
			);

			$parser->setFunctionHook( $name, $definition, $flag );

			return true;
		};

		/**
		 * https://github.com/SemanticMediaWiki/SemanticMediaWiki/blob/master/docs/technical/hooks/hook.sqlstore.beforedeletesubjectcomplete.md
		 */
		$this->handlers['SMW::SQLStore::BeforeDeleteSubjectComplete'] = static function ( $store, $title ) use ( $interlanguageLinksLookup ) {
			$interlanguageLinksLookup->setStore( $store );
			$interlanguageLinksLookup->resetLookupCacheBy( $title );

			return true;
		};

		/**
		 * https://github.com/SemanticMediaWiki/SemanticMediaWiki/blob/master/docs/technical/hooks/hook.sqlstore.beforechangetitlecomplete.md
		 */
		$this->handlers['SMW::SQLStore::BeforeChangeTitleComplete'] = static function ( $store, $oldTitle, $newTitle, $pageid, $redirid ) use ( $interlanguageLinksLookup ) {
			$interlanguageLinksLookup->setStore( $store );

			$interlanguageLinksLookup->resetLookupCacheBy( $oldTitle );
			$interlanguageLinksLookup->resetLookupCacheBy( $newTitle );

			return true;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ArticlePurge
		 */
		$this->handlers['ArticlePurge'] = static function ( &$wikiPage ) use ( $interlanguageLinksLookup ) {
			$interlanguageLinksLookup->resetLookupCacheBy(
				$wikiPage->getTitle()
			);

			return true;
		};

		/**
		 * https://www.mediawiki.org/wiki/Manual:Hooks/RevisionFromEditComplete
		 */
		$this->handlers['RevisionFromEditComplete']
		= static function ( $wikiPage ) use ( $interlanguageLinksLookup ) {
			$interlanguageLinksLookup->resetLookupCacheBy(
				$wikiPage->getTitle()
			);

			return true;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SkinTemplateGetLanguageLink
		 */
		$this->handlers['SkinTemplateGetLanguageLink'] = static function ( &$languageLink, $languageLinkTitle, $title ) {
			$siteLanguageLinkModifier = new SiteLanguageLinkModifier(
				$languageLinkTitle,
				$title
			);

			$siteLanguageLinkModifier->modifyLanguageLink( $languageLink );

			return true;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/PageContentLanguage
		 */
		$this->handlers['PageContentLanguage'] = static function ( $title, &$pageLang ) use ( $pageContentLanguageOnTheFlyModifier ) {
			$contentLang = $pageContentLanguageOnTheFlyModifier->getPageContentLanguage(
				$title,
				$pageLang
			);
			$pageLang = MediaWikiServices::getInstance()->getLanguageFactory()->getLanguage( $contentLang );

			return true;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserAfterTidy
		 */
		$this->handlers['ParserAfterTidy'] = static function ( &$parser, &$text ) {
			$parserData = ApplicationFactory::getInstance()->newParserData(
				$parser->getTitle(),
				$parser->getOutput()
			);

			$languageLinkAnnotator = new LanguageLinkAnnotator(
				$parserData
			);

			$interwikiLanguageLinkFetcher = new InterwikiLanguageLinkFetcher(
				$languageLinkAnnotator
			);

			$interwikiLanguageLinkFetcher->fetchLanguagelinksFromParserOutput(
				$parser->getOutput()
			);

			return true;
		};
	}

}
