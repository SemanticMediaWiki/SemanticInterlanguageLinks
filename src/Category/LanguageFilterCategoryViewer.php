<?php

namespace SIL\Category;

use Category;
use CategoryViewer;
use MediaWiki\Page\PageReference;
use Title;

/**
 * Modifies list of available pages based on the language a category has assigned
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class LanguageFilterCategoryViewer extends CategoryViewer {

	/**
	 * Avoid a possible category-empty message
	 *
	 * @see CategoryViewer::getCategoryTop
	 */
	public function getCategoryTop() {
		return parent::getCategoryTop() . '<span></span>';
	}

	/**
	 * @see CategoryViewer::addImage
	 */
	public function addImage(
		PageReference $page, string $sortkey, int $pageLength, bool $isRedirect = false
	): void {
		$title = Title::castFromPageIdentity( $page );
		if ( $this->canMatchCategoryLanguageToPageLanguage( $title ) ) {
			parent::addImage( $page, $sortkey, $pageLength, $isRedirect );
		}
	}

	/**
	 * @see CategoryViewer::addSubcategoryObject
	 */
	public function addSubcategoryObject( Category $cat, $sortkey, $pageLength ) {
		if ( !$this->canMatchCategoryLanguageToPageLanguage( $cat->getTitle() ) ) {
			return null;
		}

		parent::addSubcategoryObject( $cat, $sortkey, $pageLength );
	}

	/**
	 * @see CategoryViewer::addPage
	 */
	public function addPage(
		PageReference $page, string $sortkey, int $pageLength, bool $isRedirect = false
	): void {
		$title = Title::castFromPageIdentity( $page );
		if ( $this->canMatchCategoryLanguageToPageLanguage( $title ) ) {
			parent::addPage( $page, $sortkey, $pageLength, $isRedirect );
		}
	}

	private function hasInterlanguageLinksLookup() {
		return isset( Title::castFromPageIdentity( $this->page )->interlanguageLinksLookup );
	}

	private function canMatchCategoryLanguageToPageLanguage( $title ) {
		if ( !$this->hasInterlanguageLinksLookup() || !$title instanceof Title ) {
			return true;
		}

		$titleFromPage = Title::castFromPageIdentity( $this->page );

		if ( !$titleFromPage->interlanguageLinksLookup->hasSilAnnotationFor( $title ) ) {
			return false;
		}

		$categoryLanguageCode = $titleFromPage->interlanguageLinksLookup->findPageLanguageForTarget( $titleFromPage );

		if ( $categoryLanguageCode === null || $categoryLanguageCode === '' ) {
			return true;
		}

		if ( $categoryLanguageCode === $titleFromPage->interlanguageLinksLookup->findPageLanguageForTarget( $title ) ) {
			return true;
		}

		return false;
	}

}
