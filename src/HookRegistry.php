<?php

namespace SIL;

use Onoi\Cache\Cache;
use SMW\Store;
use SMW\ApplicationFactory;
use SMW\InMemoryPoolCache;
use SIL\Search\SearchResultModifier;
use SIL\Search\LanguageResultMatchFinder;
use SIL\Category\LanguageFilterCategoryPage;
use Hooks;
use Language;

/**
 * @license GNU GPL v2+
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
	 * @return boolean
	 */
	public function isRegistered( $name ) {
		return Hooks::isRegistered( $name );
	}

	/**
	 * @since  1.1
	 *
	 * @param string $name
	 *
	 * @return Callable|false
	 */
	public function getHandlerFor( $name ) {
		return isset( $this->handlers[$name] ) ? $this->handlers[$name] : false;
	}

	/**
	 * @since  1.0
	 */
	public function register() {
		foreach ( $this->handlers as $name => $callback ) {
			Hooks::register( $name, $callback );
		}
	}

	/**
	 * @since  1.0
	 *
	 * @param array &$configuration
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
		 * @see https://github.com/SemanticMediaWiki/SemanticMediaWiki/blob/master/docs/technical/hooks.md
		 */
		$this->handlers['SMW::Property::initProperties'] = function ( $baseRegistry ) {

			$propertyRegistry = new PropertyRegistry();

			$propertyRegistry->register(
				$baseRegistry
			);

			return true;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ArticleFromTitle
		 */
		$this->handlers['ArticleFromTitle'] = function ( $title, &$page ) use( $interlanguageLinksLookup ) {

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
		$this->handlers['ParserFirstCallInit'] = function ( &$parser ) use( $interlanguageLinksLookup, $pageContentLanguageOnTheFlyModifier ) {

			$parserFunctionFactory = new ParserFunctionFactory();

			list( $name, $definition, $flag ) = $parserFunctionFactory->newInterlanguageLinkParserFunctionDefinition(
				$interlanguageLinksLookup,
				$pageContentLanguageOnTheFlyModifier
			);

			$parser->setFunctionHook( $name, $definition, $flag );

			list( $name, $definition, $flag ) = $parserFunctionFactory->newInterlanguageListParserFunctionDefinition(
				$interlanguageLinksLookup
			);

			$parser->setFunctionHook( $name, $definition, $flag );

			list( $name, $definition, $flag ) = $parserFunctionFactory->newAnnotatedLanguageParserFunctionDefinition(
				$interlanguageLinksLookup
			);

			$parser->setFunctionHook( $name, $definition, $flag );

			return true;
		};

		/**
		 * https://www.mediawiki.org/wiki/Manual:Hooks/ArticleDelete
		 */
		$this->handlers['SMW::SQLStore::BeforeDeleteSubjectComplete'] = function ( $store, $title ) use ( $interlanguageLinksLookup ) {

			$interlanguageLinksLookup->setStore( $store );
			$interlanguageLinksLookup->resetLookupCacheBy( $title );

			return true;
		};

		/**
		 * https://www.mediawiki.org/wiki/Manual:Hooks/TitleMoveComplete
		 */
		$this->handlers['SMW::SQLStore::BeforeChangeTitleComplete'] = function ( $store, $oldTitle, $newTitle, $pageid, $redirid ) use ( $interlanguageLinksLookup ) {

			$interlanguageLinksLookup->setStore( $store );

			$interlanguageLinksLookup->resetLookupCacheBy( $oldTitle );
			$interlanguageLinksLookup->resetLookupCacheBy( $newTitle );

			return true;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ArticlePurge
		 */
		$this->handlers['ArticlePurge']= function ( &$wikiPage ) use ( $interlanguageLinksLookup ) {

			$interlanguageLinksLookup->resetLookupCacheBy(
				$wikiPage->getTitle()
			);

			return true;
		};

		/**
		 * https://www.mediawiki.org/wiki/Manual:Hooks/RevisionFromEditComplete
		 */
		$this->handlers['RevisionFromEditComplete']
		= function ( $wikiPage ) use ( $interlanguageLinksLookup ) {

			$interlanguageLinksLookup->resetLookupCacheBy(
				$wikiPage->getTitle()
			);

			return true;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SkinTemplateGetLanguageLink
		 */
		$this->handlers['SkinTemplateGetLanguageLink'] = function ( &$languageLink, $languageLinkTitle, $title ) {

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
		$this->handlers['PageContentLanguage'] = function ( $title, Language &$pageLang ) use ( $pageContentLanguageOnTheFlyModifier ) {

		    // PageContentLanguage now requires pageLang of type Language
			// https://phabricator.wikimedia.org/T214358
			$pageLang = Language::factory( $pageContentLanguageOnTheFlyModifier->getPageContentLanguage(
				$title,
				$pageLang
			) );

			return true;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserAfterTidy
		 */
		$this->handlers['ParserAfterTidy'] = function ( &$parser, &$text ) {

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
