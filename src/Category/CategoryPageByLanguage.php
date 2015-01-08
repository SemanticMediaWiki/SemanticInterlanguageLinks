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
	 * @var InterlanguageLinksLookup
	 */
	private $interlanguageLinksLookup = null;

	/**
	 * @since  1.0
	 *
	 * @param boolean $categoryFilterByLanguage
	 */
	public function setCategoryFilterByLanguageState( $categoryFilterByLanguage ) {
		$this->categoryFilterByLanguage = $categoryFilterByLanguage;
	}

	/**
	 * @see CategoryPage::openShowCategory
	 */
	public function openShowCategory() {

		if ( $this->interlanguageLinksLookup !== null && $this->interlanguageLinksLookup->findPageLanguageForTarget( $this->getTitle() ) !== '' ) {

			// If findPageLanguageForTarget returned a positive result
			// then Title::getPageLanguage contains the expected language
			// setting due to usage of the PageContentLanguage hook

			$html = \Html::element(
				'div',
				array(
					'id'    => 'sil-categorypage-languagefilter',
					'style' => 'font-style:italic' ),
				wfMessage( 'sil-categorypage-languagefilter-active' )->inLanguage( $this->getTitle()->getPageLanguage() )->text()
			);

			$this->getContext()->getOutput()->addHTML( $html );
		}
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

		$this->interlanguageLinksLookup = $interlanguageLinksLookup;

		// @note Need to attach the InterlanguageLinksLookup to the title
		// as it is the only way to inject a dependency by the time the
		// CategoryViewerByLanguage object is created
		$this->getTitle()->interlanguageLinksLookup = $this->interlanguageLinksLookup;
	}

}
