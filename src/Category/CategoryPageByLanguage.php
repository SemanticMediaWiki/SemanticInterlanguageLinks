<?php

namespace SIL\Category;

use SIL\InterlanguageLinksLookup;

use CategoryPage;

/**
 * Modifies the content display of a category page
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CategoryPageByLanguage extends CategoryPage {

	/**
	 * @see CategoryPage::$mCategoryViewerClass
	 */
	protected $mCategoryViewerClass = '\SIL\Category\CategoryViewerByLanguage';

	/**
	 * @var boolean
	 */
	private $categoryFilterByLanguage = true;

	/**
	 * @since  1.0
	 *
	 * @param boolean $categoryFilterByLanguage
	 */
	public function setCategoryFilterByLanguageState( $categoryFilterByLanguage ) {
		$this->categoryFilterByLanguage = $categoryFilterByLanguage;
	}

	/**
	 * @since 1.0
	 *
	 * @param Page &$page
	 * @param InterlanguageLinksLookup $interlanguageLinksLookup
	 */
	public function modifyCategoryView( &$page, InterlanguageLinksLookup $interlanguageLinksLookup ) {

		if ( !$this->categoryFilterByLanguage || $this->getTitle()->getNamespace() !== NS_CATEGORY ) {
			return null;
		}

		$page = $this;

		// @note Need to attach the InterlanguageLinksLookup to the title
		// as it is the only way to inject a dependency by the time the
		// CategoryViewerByLanguage object is created
		$this->getTitle()->interlanguageLinksLookup = $interlanguageLinksLookup;
	}

}
