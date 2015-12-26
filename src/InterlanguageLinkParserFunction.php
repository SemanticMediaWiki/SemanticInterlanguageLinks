<?php

namespace SIL;

use Onoi\Cache\CacheFactory;
use Title;
use Language;

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
	 * @var boolean
	 */
	private $interlanguageLinksHideState = false;

	/**
	 * @since 1.0
	 *
	 * @param Title $title
	 * @param LanguageLinkAnnotator $languageLinkAnnotator
	 * @param SiteLanguageLinksParserOutputAppender $siteLanguageLinksParserOutputAppender
	 */
	public function __construct( Title $title, LanguageLinkAnnotator $languageLinkAnnotator, SiteLanguageLinksParserOutputAppender $siteLanguageLinksParserOutputAppender ) {
		$this->title = $title;
		$this->languageLinkAnnotator = $languageLinkAnnotator;
		$this->siteLanguageLinksParserOutputAppender = $siteLanguageLinksParserOutputAppender;
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
	 * @since 1.0
	 *
	 * @param string $languageCode
	 * @param string $linkReference
	 *
	 * @return null|string
	 */
	public function parse( $languageCode, $linkReference ) {

		if ( $this->interlanguageLinksHideState ) {
			return $this->createErrorMessageFor( 'sil-interlanguagelink-hideinterlanguagelinks' );
		}

		if ( !$this->isSupportedLanguage( $languageCode ) ) {
			return $this->createErrorMessageFor( 'sil-interlanguagelink-invalidlanguagecode', $languageCode );
		}

		$title = Title::newFromText( $linkReference );

		if ( $title === null ) {
			return $this->createErrorMessageFor( 'sil-interlanguageparser-linkreference-error', $linkReference );
		}

		$interlanguageLink = new InterlanguageLink(
			wfBCP47( $languageCode ),
			$this->siteLanguageLinksParserOutputAppender->getRedirectTargetFor( $title )
		);

		if ( $this->languageLinkAnnotator->hasDifferentLanguageAnnotation( $interlanguageLink ) ) {
			return $this->createErrorMessageFor( 'sil-interlanguagelink-multiplecalls-different-languagecode', $languageCode );
		}

		return $this->createSiteLanguageLinks( $interlanguageLink );
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

		$languageCode = trim( $languageCode );

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
