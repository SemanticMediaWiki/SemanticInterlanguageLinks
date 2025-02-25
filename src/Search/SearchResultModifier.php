<?php

namespace SIL\Search;

use Html;
use MediaWiki\MediaWikiServices;
use SearchResultSet;
use SMW\Localizer\Localizer;
use SpecialSearch;
use Xml;
use XmlSelect;

/**
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class SearchResultModifier {

	/**
	 * @var LanguageResultMatchFinder|null
	 */
	private $languageResultMatchFinder = null;

	/**
	 * @since 1.0
	 *
	 * @param LanguageResultMatchFinder $languageResultMatchFinder
	 */
	public function __construct( LanguageResultMatchFinder $languageResultMatchFinder ) {
		$this->languageResultMatchFinder = $languageResultMatchFinder;
	}

	/**
	 * @since 1.0
	 *
	 * @param array &$profiles
	 */
	public function addSearchProfile( array &$profiles ) {
		$profiles['sil'] = [
			'message' => 'sil-search-profile',
			'tooltip' => 'sil-search-profile-tooltip',
			'namespaces' => MediaWikiServices::getInstance()->getSearchEngineConfig()
														   ->defaultNamespaces()

		];

		return true;
	}

	/**
	 * @since 1.0
	 *
	 * @param SpecialSearch $search
	 * @param string $profile
	 * @param string &$form
	 * @param array $opts
	 *
	 * @return bool
	 */
	public function addSearchProfileForm( SpecialSearch $search, $profile, &$form, $opts ) {
		if ( $profile !== 'sil' ) {
			return true;
		}

		$hidden = '';

		foreach ( $opts as $key => $value ) {
			$hidden .= Html::hidden( $key, $value );
		}

		$languagefilter = $search->getContext()->getRequest()->getVal( 'languagefilter' );

		if ( $languagefilter !== '' && $languagefilter !== null ) {
			$search->setExtraParam( 'languagefilter', $languagefilter );
		}

		$params = [ 'id' => 'mw-searchoptions' ];

		$form = Xml::fieldset( false, false, $params ) .
			$hidden . $this->createHtmlLanguageFilterSelector( $languagefilter ) .
			Html::closeElement( 'fieldset' );

		return false;
	}

	/**
	 * @since 1.0
	 *
	 * @param WebRequest $request
	 * @param array &$showSections
	 *
	 * @return bool
	 */
	public function addLanguageFilterToPowerBox( $request, &$showSections ) {
		$showSections['sil-languagefilter'] = $this->createHtmlLanguageFilterSelector(
			$request->getVal( 'languagefilter' )
		);

		return true;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $defaultLanguagefilter
	 *
	 * @return string
	 */
	public function createHtmlLanguageFilterSelector( $defaultLanguagefilter ) {
		$languages = MediaWikiServices::getInstance()->getLanguageNameUtils()->getLanguageNames();

		ksort( $languages );

		$selector = new XmlSelect( 'languagefilter', 'languagefilter' );
		$selector->setDefault( $defaultLanguagefilter );
		$selector->addOption( wfMessage( 'sil-search-nolanguagefilter' )->text(), '-' );

		foreach ( $languages as $code => $name ) {
			$selector->addOption( "$code - $name", $code );
		}

		$selector = $selector->getHTML();

		$label = Xml::label(
			wfMessage( 'sil-search-languagefilter-label' )->text(),
			'languagefilter'
		) . '&#160;';

		return $label . $selector;
	}

	/**
	 * @since 1.0
	 *
	 * @param WebRequest $request
	 * @param SearchResultSet|false &$titleMatches
	 * @param SearchResultSet|false &$textMatches
	 *
	 * @return bool
	 */
	public function applyLanguageFilterToResultMatches( $request, &$titleMatches, &$textMatches ) {
		if ( !in_array( $request->getVal( 'profile' ), [ 'sil', 'advanced' ] ) ) {
			return false;
		}

		$languageCode = Localizer::asBCP47FormattedLanguageCode( $request->getVal( 'languagefilter' ) );

		if ( in_array( $languageCode, [ null, '', '-' ] ) ) {
			return false;
		}

		if ( $titleMatches instanceof SearchResultSet ) {
			$titleMatches = $this->languageResultMatchFinder->matchResultsToLanguage(
				$titleMatches,
				$languageCode
			);
		}

		if ( $textMatches instanceof SearchResultSet ) {
			$textMatches = $this->languageResultMatchFinder->matchResultsToLanguage(
				$textMatches,
				$languageCode
			);
		}

		return true;
	}

}
