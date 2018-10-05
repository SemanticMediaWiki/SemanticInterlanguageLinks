<?php

namespace SIL\Category;

use SIL\InterlanguageLinksLookup;
use CategoryPage;
use Title;
use Html;

/**
 * Modifies the content display of a category page
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class LanguageFilterCategoryPage extends CategoryPage {

	/**
	 * @see CategoryPage::$mCategoryViewerClass
	 */
	protected $mCategoryViewerClass = '\SIL\Category\LanguageFilterCategoryViewer';

	/**
	 * @var boolean
	 */
	private $isCategoryFilterByLanguage = true;

	/**
	 * @var InterlanguageLinksLookup
	 */
	private $interlanguageLinksLookup = null;

	/**
	 * @since  1.0
	 *
	 * @param boolean $isCategoryFilterByLanguage
	 */
	public function isCategoryFilterByLanguage( $isCategoryFilterByLanguage ) {
		$this->isCategoryFilterByLanguage = $isCategoryFilterByLanguage;
	}

	/**
	 * @see CategoryPage::openShowCategory
	 */
	public function openShowCategory() {

		if ( $this->hasPageLanguageForTarget( $this->getTitle() ) ) {

			// If findPageLanguageForTarget returned a positive result
			// then Title::getPageLanguage contains the expected language
			// setting due to usage of the PageContentLanguage hook

			$html = Html::element(
				'div',
				[
					'id'    => 'sil-categorypage-languagefilter',
					'style' => 'font-style:italic'
				],
				wfMessage(
					'sil-categorypage-languagefilter-active',
					$this->getTitle()->getPageLanguage()->getCode() )->inLanguage( $this->getTitle()->getPageLanguage() )->text()
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

		if ( $this->getTitle()->getNamespace() !== NS_CATEGORY ||
			!$this->isCategoryFilterByLanguage ||
			!$interlanguageLinksLookup->hasSilAnnotationFor( $this->getTitle() ) ) {
			return null;
		}

		$page = $this;

		$this->interlanguageLinksLookup = $interlanguageLinksLookup;

		// @note Need to attach the InterlanguageLinksLookup to the title
		// as it is the only way to inject a dependency by the time the
		// CategoryViewerByLanguage object is created
		$this->getTitle()->interlanguageLinksLookup = $this->interlanguageLinksLookup;
	}

	private function hasPageLanguageForTarget( Title $title ) {
		return $this->interlanguageLinksLookup !== null &&
			$this->interlanguageLinksLookup->findPageLanguageForTarget( $title ) !== '' &&
			$this->interlanguageLinksLookup->hasSilAnnotationFor( $title );
	}

}
