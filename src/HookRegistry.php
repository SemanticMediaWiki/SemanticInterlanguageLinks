<?php

namespace SIL;

use Onoi\Cache\Cache;

use SMW\Store;
use SIL\Search\SearchResultModifier;
use SIL\Search\LanguageResultMatchFinder;
use SIL\Category\ByLanguageCategoryPage;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class HookRegistry {

	/**
	 * @var Store
	 */
	private $store;

	/**
	 * @var Cache
	 */
	private $cache;

	/**
	 * @var string
	 */
	private $cachePrefix;

	/**
	 * @since 1.0
	 *
	 * @param Store $store
	 * @param Cache $cache
	 * @param string $cachePrefix
	 */
	public function __construct( Store $store, Cache $cache, $cachePrefix ) {
		$this->store = $store;
		$this->cache = $cache;
		$this->cachePrefix = $cachePrefix;
	}

	/**
	 * @since  1.0
	 *
	 * @param array &$wgHooks
	 *
	 * @return boolean
	 */
	public function register( &$wgHooks ) {

		$cacheKeyGenerator = new CacheKeyGenerator();
		$cacheKeyGenerator->setAuxiliaryVersionModifier( '20150122' );
		$cacheKeyGenerator->setCachePrefix( $this->cachePrefix );

		$languageTargetLinksCache = new LanguageTargetLinksCache(
			$this->cache,
			$cacheKeyGenerator
		);

		$interlanguageLinksLookup = new InterlanguageLinksLookup(
			$languageTargetLinksCache
		);

		$interlanguageLinksLookup->setStore( $this->store );

		$searchResultModifier = new SearchResultModifier(
			new LanguageResultMatchFinder( $interlanguageLinksLookup )
		);

		$propertyRegistry = new PropertyRegistry();

		/**
		 * @see https://github.com/SemanticMediaWiki/SemanticMediaWiki/blob/master/docs/technical/hooks.md
		 */
		$wgHooks['smwInitProperties'][] = function () use ( $propertyRegistry ) {
			return $propertyRegistry->register();
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ArticleFromTitle
		 */
		$wgHooks['ArticleFromTitle'][] = function ( $title, &$page ) use( $interlanguageLinksLookup ) {

			$byLanguageCategoryPage = new ByLanguageCategoryPage( $title );
			$byLanguageCategoryPage->setCategoryFilterByLanguageState( $GLOBALS['egSILUseCategoryFilterByLanguage'] );
			$byLanguageCategoryPage->modifyCategoryView( $page, $interlanguageLinksLookup );

			return true;
		};

		$this->registerInterlanguageParserHooks( $interlanguageLinksLookup, $wgHooks );
		$this->registerSpecialSearchHooks( $searchResultModifier, $wgHooks );

		return true;
	}

	private function registerInterlanguageParserHooks( InterlanguageLinksLookup $interlanguageLinksLookup, &$wgHooks ) {

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
		 */
		$wgHooks['ParserFirstCallInit'][] = function ( &$parser ) use( $interlanguageLinksLookup ) {

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
		$wgHooks['SMW::SQLStore::BeforeDeleteSubjectComplete'][] = function ( $store, $title ) use ( $interlanguageLinksLookup ) {

			$interlanguageLinksLookup->setStore( $store );
			$interlanguageLinksLookup->invalidateLookupCache( $title );

			return true;
		};

		/**
		 * https://www.mediawiki.org/wiki/Manual:Hooks/TitleMoveComplete
		 */
		$wgHooks['SMW::SQLStore::BeforeChangeTitleComplete'][] = function ( $store, $oldTitle, $newTitle, $pageid, $redirid ) use ( $interlanguageLinksLookup ) {

			$interlanguageLinksLookup->setStore( $store );

			$interlanguageLinksLookup->invalidateLookupCache( $oldTitle );
			$interlanguageLinksLookup->invalidateLookupCache( $newTitle );

			return true;
		};

		/**
		 * https://www.mediawiki.org/wiki/Manual:Hooks/NewRevisionFromEditComplete
		 */
		$wgHooks['NewRevisionFromEditComplete'][] = function ( $wikiPage ) use ( $interlanguageLinksLookup ) {

			$interlanguageLinksLookup->invalidateLookupCache( $wikiPage->getTitle() );

			return true;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SkinTemplateGetLanguageLink
		 */
		$wgHooks['SkinTemplateGetLanguageLink'][] = function ( &$languageLink, $languageLinkTitle, $title ) {

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
		$wgHooks['PageContentLanguage'][] = function ( $title, &$pageLang ) use ( $interlanguageLinksLookup ) {

			$pageContentLanguageModifier = new PageContentLanguageModifier(
				$interlanguageLinksLookup,
				$title
			);

			$pageContentLanguageModifier->modifyLanguage( $pageLang );

			return true;
		};
	}

	private function registerSpecialSearchHooks( SearchResultModifier $searchResultModifier, &$wgHooks ) {

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SpecialSearchProfiles
		 */
		$wgHooks['SpecialSearchProfiles'][] = function ( array &$profiles ) use ( $searchResultModifier ) {

			$searchProfile = $searchResultModifier->addSearchProfile(
				$profiles
			);

			return $searchProfile;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SpecialSearchProfileForm
		 */
		$wgHooks['SpecialSearchProfileForm'][] = function ( $search, &$form, $profile, $term, $opts ) use ( $searchResultModifier ) {

			$searchProfileForm = $searchResultModifier->addSearchProfileForm(
				$search,
				$profile,
				$form,
				$opts
			);

			return $searchProfileForm;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SpecialSearchResults
		 */
		$wgHooks['SpecialSearchResults'][] = function ( $term, &$titleMatches, &$textMatches ) use ( $searchResultModifier ) {

			$resultMatches = $searchResultModifier->applyLanguageFilterToResultMatches(
				$GLOBALS['wgRequest'],
				$titleMatches,
				$textMatches
			);

			return $resultMatches;
		};
	}

}
