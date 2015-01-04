<?php

namespace SIL;

use SMW\Store;
use SIL\Search\SearchResultModifier;
use SIL\Search\LanguageResultMatchFinder;

use Parser;
use BagOStuff;

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
	 * @var BagOStuff
	 */
	private $cache;

	/**
	 * @since 1.0
	 *
	 * @param Store $store
	 * @param BagOStuff $cache
	 */
	public function __construct( Store $store, BagOStuff $cache ) {
		$this->store = $store;
		$this->cache = $cache;
	}

	/**
	 * @since  1.0
	 *
	 * @param array &$wgHooks
	 *
	 * @return boolean
	 */
	public function register( &$wgHooks ) {

		$cachedLanguageTargetLinks = new LanguageTargetLinkCache(
			$this->cache
		);

		$interlanguageLinksLookup = new InterlanguageLinksLookup(
			$cachedLanguageTargetLinks
		);

		$interlanguageLinksLookup->setStore( $this->store );

		$searchResultModifier = new SearchResultModifier(
			new LanguageResultMatchFinder( $interlanguageLinksLookup )
		);

		/**
		 * @see https://github.com/SemanticMediaWiki/SemanticMediaWiki/blob/master/docs/technical/hooks.md
		 */
		$wgHooks['smwInitProperties'][] = function () {
			return PropertyRegistry::register();
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
		 */
		$wgHooks['ParserFirstCallInit'][] = function ( &$parser ) use( $interlanguageLinksLookup ) {

			$parserFunctionFactory = new ParserFunctionFactory();

			list( $name, $definition, $flag ) = $parserFunctionFactory->newInterlanguageLinkParserFunction(
				$interlanguageLinksLookup
			);

			$parser->setFunctionHook( $name, $definition, $flag );

			list( $name, $definition, $flag ) = $parserFunctionFactory->newInterlanguageListParserFunction(
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
			$interlanguageLinksLookup->doInvalidateCachedLanguageTargetLinks( $title );

			return true;
		};

		/**
		 * https://www.mediawiki.org/wiki/Manual:Hooks/TitleMoveComplete
		 */
		$wgHooks['SMW::SQLStore::BeforeChangeTitleComplete'][] = function ( $store, $oldTitle, $newTitle, $pageid, $redirid ) use ( $interlanguageLinksLookup ) {

			$interlanguageLinksLookup->setStore( $store );

			$interlanguageLinksLookup->doInvalidateCachedLanguageTargetLinks( $oldTitle );
			$interlanguageLinksLookup->doInvalidateCachedLanguageTargetLinks( $newTitle );

			return true;
		};

		/**
		 * https://www.mediawiki.org/wiki/Manual:Hooks/NewRevisionFromEditComplete
		 */
		$wgHooks['NewRevisionFromEditComplete'][] = function ( $wikiPage ) use ( $interlanguageLinksLookup ) {

			$interlanguageLinksLookup->doInvalidateCachedLanguageTargetLinks( $wikiPage->getTitle() );

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

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SpecialSearchProfiles
		 */
		$wgHooks['SpecialSearchProfiles'][] = function ( array &$profiles ) use ( $searchResultModifier ) {
			return $searchResultModifier->addSearchProfile( $profiles );
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SpecialSearchProfileForm
		 */
		$wgHooks['SpecialSearchProfileForm'][] = function ( $search, &$form, $profile, $term, $opts ) use ( $searchResultModifier ) {
			return $searchResultModifier->addSearchProfileForm( $search, $profile, $form, $opts );
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SpecialSearchResults
		 */
		$wgHooks['SpecialSearchResults'][] = function ( $term, &$titleMatches, &$textMatches ) use ( $searchResultModifier ) {
			return $searchResultModifier->applyPostFilterToResultMatches( $GLOBALS['wgRequest'], $titleMatches, $textMatches );
		};

		return true;
	}

}
