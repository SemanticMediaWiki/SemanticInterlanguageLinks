<?php

namespace SIL\Category;

use CategoryViewer;
use Title;
use Category;

/**
 * Modifies list of available pages based on the language a category has assigned
 *
 * @license GNU GPL v2+
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
	public function addImage( Title $title, $sortkey, $pageLength, $isRedirect = false ) {

		if ( !$this->canMatchCategoryLanguageToPageLanguage( $title ) ) {
			return null;
		}

		parent::addImage( $title, $sortkey, $pageLength, $isRedirect );
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
	public function addPage( $title, $sortkey, $pageLength, $isRedirect = false ) {

		if ( !$this->canMatchCategoryLanguageToPageLanguage( $title ) ) {
			return null;
		}

		parent::addPage( $title, $sortkey, $pageLength, $isRedirect );
	}

	private function hasInterlanguageLinksLookup() {
		return isset( $this->title->interlanguageLinksLookup );
	}

	private function canMatchCategoryLanguageToPageLanguage( $title ) {

		if ( !$this->hasInterlanguageLinksLookup() || !$title instanceOf Title ) {
			return true;
		}

		if ( !$this->title->interlanguageLinksLookup->hasSilAnnotationFor( $title ) ) {
			return false;
		}

		$categoryLanguageCode = $this->title->interlanguageLinksLookup->findPageLanguageForTarget( $this->title );

		if ( $categoryLanguageCode === null || $categoryLanguageCode === '' ) {
			return true;
		}

		if ( $categoryLanguageCode === $this->title->interlanguageLinksLookup->findPageLanguageForTarget( $title ) ) {
			return true;
		}

		return false;
	}

}
