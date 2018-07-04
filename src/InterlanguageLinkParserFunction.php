<?php

namespace SIL;

use Onoi\Cache\CacheFactory;
use Title;
use Language;

use SMW\Localizer;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class InterlanguageLinkParserFunction {

	/**
	 * @var Title
	 */
	private $title;

	/**
	 * @var LanguageLinkAnnotator
	 */
	private $languageLinkAnnotator;

	/**
	 * @var SiteLanguageLinksParserOutputAppender
	 */
	private $siteLanguageLinksParserOutputAppender;

	/**
	 * @var pageContentLanguageOnTheFlyModifier
	 */
	private $pageContentLanguageOnTheFlyModifier;

	/**
	 * @var PageContentLanguageDbModifier
	 */
	private $pageContentLanguageDbModifier;

	/**
	 * @var boolean
	 */
	private $interlanguageLinksHideState = false;

	/**
	 * @var boolean
	 */
	private $inRevisionMode = false;

	/**
	 * @since 1.0
	 *
	 * @param Title $title
	 * @param LanguageLinkAnnotator $languageLinkAnnotator
	 * @param SiteLanguageLinksParserOutputAppender $siteLanguageLinksParserOutputAppender
	 * @param PageContentLanguageOnTheFlyModifier $pageContentLanguageOnTheFlyModifier
	 * @param PageContentLanguageDbModifier $pageContentLanguageDbModifier
	 */
	public function __construct( Title $title, LanguageLinkAnnotator $languageLinkAnnotator, SiteLanguageLinksParserOutputAppender $siteLanguageLinksParserOutputAppender, PageContentLanguageOnTheFlyModifier $pageContentLanguageOnTheFlyModifier, PageContentLanguageDbModifier $pageContentLanguageDbModifier ) {
		$this->title = $title;
		$this->languageLinkAnnotator = $languageLinkAnnotator;
		$this->siteLanguageLinksParserOutputAppender = $siteLanguageLinksParserOutputAppender;
		$this->pageContentLanguageOnTheFlyModifier = $pageContentLanguageOnTheFlyModifier;
		$this->pageContentLanguageDbModifier = $pageContentLanguageDbModifier;
	}

	/**
	 * @since 1.0
	 *
	 * @param boolean $interlanguageLinksHideState
	 */
	public function setInterlanguageLinksHideState( $interlanguageLinksHideState ) {
		$this->interlanguageLinksHideState = $interlanguageLinksHideState;
	}

	/**
	 * Revision mode means either in preview or edit state which is not to be
	 * handled to avoid storage of yet unprocessed data in cache.
	 *
	 * @since 1.2
	 *
	 * @param boolean $inRevisionMode
	 */
	public function setRevisionModeState( $inRevisionMode ) {
		$this->inRevisionMode = $inRevisionMode;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $languageCode
	 * @param string $linkReference
	 *
	 * @return null|string
	 */
	public function parse( $languageCode, $linkReference ) {

		$languageCode = Localizer::asBCP47FormattedLanguageCode( $languageCode );

		// Keep reference while editing is on going to avoid a possible lag when
		// a DV is trying to access the page content language
		if ( ( $isSupportedLanguage = $this->isSupportedLanguage( $languageCode ) ) === true ) {
			$this->pageContentLanguageOnTheFlyModifier->addToIntermediaryCache( $this->title, $languageCode );
		}

		if ( !( $title = $this->getTitleFrom( $isSupportedLanguage, $languageCode, $linkReference ) ) instanceof Title ) {
			return $title;
		}

		$interlanguageLink = new InterlanguageLink(
			$languageCode,
			$this->siteLanguageLinksParserOutputAppender->getRedirectTargetFor( $title )
		);

		$this->pageContentLanguageDbModifier->updatePageLanguage( $languageCode );

		if ( $this->languageLinkAnnotator->hasDifferentLanguageAnnotation( $interlanguageLink ) ) {
			return $this->createErrorMessageFor( 'sil-interlanguagelink-multiplecalls-different-languagecode', $languageCode );
		}

		return $this->createSiteLanguageLinks( $interlanguageLink );
	}

	private function getTitleFrom( $isSupportedLanguage, $languageCode, $linkReference ) {

		if ( $this->inRevisionMode || !$this->languageLinkAnnotator->canAddAnnotation() ) {
			return '';
		}

		if ( $this->interlanguageLinksHideState ) {
			return $this->createErrorMessageFor( 'sil-interlanguagelink-hideinterlanguagelinks' );
		}

		if ( !$isSupportedLanguage ) {
			return $this->createErrorMessageFor( 'sil-interlanguagelink-invalidlanguagecode', $languageCode );
		}

		$title = Title::newFromText( $linkReference );

		if ( $title === null ) {
			return $this->createErrorMessageFor( 'sil-interlanguageparser-linkreference-error', $linkReference );
		}

		return $title;
	}

	private function createSiteLanguageLinks( InterlanguageLink $interlanguageLink ) {

		$knownTargetLink = $this->siteLanguageLinksParserOutputAppender->tryAddLanguageTargetLinksToOutput(
			$interlanguageLink,
			$this->title
		);

		// If target is known we stop processing and output an error
		if ( $knownTargetLink ) {
			return $this->createErrorMessageFor(
				'sil-interlanguagelink-languagetargetcombination-exists',
				$interlanguageLink->getLanguageCode(),
				$interlanguageLink->getLinkReference()->getPrefixedText(),
				$knownTargetLink,
				$this->title->getPrefixedText()
			);
		}

		$this->languageLinkAnnotator->addAnnotationForInterlanguageLink(
			$interlanguageLink
		);

		return '<div class="sil-interlanguagelink"></div>';
	}

	private function isSupportedLanguage( $languageCode ) {

		$languageCode = trim( mb_strtolower( $languageCode ) );

		if ( strlen( $languageCode ) == 0 ) {
			return false;
		}

		return Language::isSupportedLanguage( $languageCode );
	}

	private function createErrorMessageFor( $messageKey, $arg1 = '', $arg2 = '', $arg3 = '',$arg4 = '' ) {
		return '<div class="smw-callout smw-callout-error">' . wfMessage(
			$messageKey,
			$arg1,
			$arg2,
			$arg3,
			$arg4
		)->inContentLanguage()->text() . '</div>';
	}

}
