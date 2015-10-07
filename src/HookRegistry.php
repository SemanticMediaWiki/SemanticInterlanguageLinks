<?php

namespace SIL;

use Onoi\Cache\Cache;

use SMW\Store;
use SMW\ApplicationFactory;
use SIL\Search\SearchResultModifier;
use SIL\Search\LanguageResultMatchFinder;
use SIL\Category\ByLanguageCategoryPage;
use Hooks;

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
	private $handlers = array();

	/**
	 * @since 1.0
	 *
	 * @param Store $store
	 * @param Cache $cache
	 * @param string $cachePrefix
	 */
	public function __construct( Store $store, Cache $cache, $cachePrefix ) {
		$this->addCallbackHandlers( $store, $cache, $cachePrefix );
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

	private function addCallbackHandlers( $store, $cache, $cachePrefix ) {

		$cacheKeyProvider = new CacheKeyProvider();
		$cacheKeyProvider->setCachePrefix( $cachePrefix );

		$languageTargetLinksCache = new LanguageTargetLinksCache(
			$cache,
			$cacheKeyProvider
		);

		$interlanguageLinksLookup = new InterlanguageLinksLookup(
			$languageTargetLinksCache
		);

		$interlanguageLinksLookup->setStore( $store );

		$propertyRegistry = new PropertyRegistry();

		/**
		 * @see https://github.com/SemanticMediaWiki/SemanticMediaWiki/blob/master/docs/technical/hooks.md
		 */
		$this->handlers['SMW::Property::initProperties'] = function () use ( $propertyRegistry ) {
			return $propertyRegistry->register();
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ArticleFromTitle
		 */
		$this->handlers['ArticleFromTitle'] = function ( $title, &$page ) use( $interlanguageLinksLookup ) {

			$byLanguageCategoryPage = new ByLanguageCategoryPage( $title );
			$byLanguageCategoryPage->setCategoryFilterByLanguageState( $GLOBALS['egSILEnabledCategoryFilterByLanguage'] );
			$byLanguageCategoryPage->modifyCategoryView( $page, $interlanguageLinksLookup );

			return true;
		};

		$this->registerInterlanguageParserHooks( $interlanguageLinksLookup );
		$this->registerSpecialSearchHooks( $interlanguageLinksLookup );
	}

	private function registerInterlanguageParserHooks( InterlanguageLinksLookup $interlanguageLinksLookup ) {

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
		 */
		$this->handlers['ParserFirstCallInit'] = function ( &$parser ) use( $interlanguageLinksLookup ) {

			$parserFunctionFactory = new ParserFunctionFactory();

			list( $name, $definition, $flag ) = $parserFunctionFactory->newInterlanguageLinkParserFunctionDefinition(
				$interlanguageLinksLookup
			);

			$parser->setFunctionHook( $name, $definition, $flag );

			list( $name, $definition, $flag ) = $parserFunctionFactory->newInterlanguageListParserFunctionDefinition(
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
			$interlanguageLinksLookup->invalidateLookupCache( $title );

			return true;
		};

		/**
		 * https://www.mediawiki.org/wiki/Manual:Hooks/TitleMoveComplete
		 */
		$this->handlers['SMW::SQLStore::BeforeChangeTitleComplete'] = function ( $store, $oldTitle, $newTitle, $pageid, $redirid ) use ( $interlanguageLinksLookup ) {

			$interlanguageLinksLookup->setStore( $store );

			$interlanguageLinksLookup->invalidateLookupCache( $oldTitle );
			$interlanguageLinksLookup->invalidateLookupCache( $newTitle );

			return true;
		};

		/**
		 * https://www.mediawiki.org/wiki/Manual:Hooks/NewRevisionFromEditComplete
		 */
		$this->handlers['NewRevisionFromEditComplete'] = function ( $wikiPage ) use ( $interlanguageLinksLookup ) {

			$interlanguageLinksLookup->invalidateLookupCache(
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
		$this->handlers['PageContentLanguage'] = function ( $title, &$pageLang ) use ( $interlanguageLinksLookup ) {

			$pageContentLanguageModifier = new PageContentLanguageModifier(
				$interlanguageLinksLookup,
				$title
			);

			$pageContentLanguageModifier->modifyLanguage( $pageLang );

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

	private function registerSpecialSearchHooks( InterlanguageLinksLookup $interlanguageLinksLookup ) {

		$searchResultModifier = new SearchResultModifier(
			new LanguageResultMatchFinder( $interlanguageLinksLookup )
		);

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SpecialSearchProfiles
		 */
		$this->handlers['SpecialSearchProfiles'] = function ( array &$profiles ) use ( $searchResultModifier ) {

			$searchProfile = $searchResultModifier->addSearchProfile(
				$profiles
			);

			return $searchProfile;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SpecialSearchProfileForm
		 */
		$this->handlers['SpecialSearchProfileForm'] = function ( $search, &$form, $profile, $term, $opts ) use ( $searchResultModifier ) {

			$searchProfileForm = $searchResultModifier->addSearchProfileForm(
				$search,
				$profile,
				$form,
				$opts
			);

			return $searchProfileForm;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SpecialSearchPowerBox
		 */
		$this->handlers['SpecialSearchPowerBox'] = function ( &$showSections, $term, $opts ) use ( $searchResultModifier ) {

			$searchResultModifier->addLanguageFilterToPowerBox(
				$GLOBALS['wgRequest'],
				$showSections
			);

			return true;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SpecialSearchResults
		 */
		$this->handlers['SpecialSearchResults'] = function ( $term, &$titleMatches, &$textMatches ) use ( $searchResultModifier ) {

			$resultMatches = $searchResultModifier->applyLanguageFilterToResultMatches(
				$GLOBALS['wgRequest'],
				$titleMatches,
				$textMatches
			);

			return $resultMatches;
		};
	}

}
