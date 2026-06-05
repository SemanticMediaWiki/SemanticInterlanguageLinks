<?php

namespace SIL\Hooks;

use MediaWiki\Content\Hook\PageContentLanguageHook;
use MediaWiki\Hook\SkinTemplateGetLanguageLinkHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\Hook\ArticleFromTitleHook;
use SIL\Category\LanguageFilterCategoryPage;
use SIL\InterlanguageLinksLookup;
use SIL\PageContentLanguageOnTheFlyModifier;
use SIL\SiteLanguageLinkModifier;

/**
 * Applies the SIL annotation to language-aware page display: the category
 * language filter, the on-the-fly page content language and the sidebar
 * language links.
 *
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */
class PageLanguageHooks implements ArticleFromTitleHook, PageContentLanguageHook, SkinTemplateGetLanguageLinkHook {

	public function __construct(
		private readonly InterlanguageLinksLookup $interlanguageLinksLookup,
		private readonly PageContentLanguageOnTheFlyModifier $pageContentLanguageOnTheFlyModifier
	) {
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ArticleFromTitle
	 */
	public function onArticleFromTitle( $title, &$article, $context ): bool {
		$languageFilterCategoryPage = new LanguageFilterCategoryPage( $title );
		$languageFilterCategoryPage->isCategoryFilterByLanguage( $GLOBALS['silgEnabledCategoryFilterByLanguage'] );
		$languageFilterCategoryPage->modifyCategoryView( $article, $this->interlanguageLinksLookup );

		return true;
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/PageContentLanguage
	 */
	public function onPageContentLanguage( $title, &$pageLang, $userLang ): bool {
		$contentLang = $this->pageContentLanguageOnTheFlyModifier->getPageContentLanguage(
			$title,
			$pageLang
		);

		$pageLang = MediaWikiServices::getInstance()->getLanguageFactory()->getLanguage( $contentLang );

		return true;
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SkinTemplateGetLanguageLink
	 */
	public function onSkinTemplateGetLanguageLink( &$languageLink, $languageLinkTitle, $title, $outputPage ): bool {
		$siteLanguageLinkModifier = new SiteLanguageLinkModifier(
			$languageLinkTitle,
			$title
		);

		$siteLanguageLinkModifier->modifyLanguageLink( $languageLink );

		return true;
	}

}
