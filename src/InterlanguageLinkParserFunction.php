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
	 * @var SiteLanguageLinksGenerator
	 */
	private $siteLanguageLinksGenerator;

	/**
	 * @var boolean
	 */
	private $hideInterlanguageLinks = false;

	/**
	 * A static tracker to identify whether INTERLANGUAGLINK has been
	 * called more than once on the target page
	 *
	 * @var FixedInMemoryCache|null
	 */
	private static $inMemoryParserTracker = null;

	/**
	 * @since 1.0
	 *
	 * @param Title $title
	 * @param LanguageLinkAnnotator $languageLinkAnnotator
	 * @param SiteLanguageLinksGenerator $siteLanguageLinksGenerator
	 */
	public function __construct( Title $title, LanguageLinkAnnotator $languageLinkAnnotator, SiteLanguageLinksGenerator $siteLanguageLinksGenerator ) {
		$this->title = $title;
		$this->languageLinkAnnotator = $languageLinkAnnotator;
		$this->siteLanguageLinksGenerator = $siteLanguageLinksGenerator;
	}

	/**
	 * @since 1.0
	 *
	 * @param boolean $hideInterlanguageLinks
	 */
	public function setInterlanguageLinksState( $hideInterlanguageLinks ) {
		$this->hideInterlanguageLinks = $hideInterlanguageLinks;
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

		if ( $this->hideInterlanguageLinks ) {
			return $this->createErrorMessageFor( 'sil-interlanguagelink-hideinterlanguagelinks' );
		}

		if ( ( $result = $this->checkIfIMultipleParserCallsOccurred( $languageCode, $linkReference ) ) !== false ) {
			return $result;
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
			$title
		);

		return $this->createSiteLanguageLinks( $interlanguageLink );
	}

	private function checkIfIMultipleParserCallsOccurred( $languageCode, $linkReference ) {

		if ( !$this->getInMemoryParserTracker()->contains( $this->title->getPrefixedDBKey() ) ) {
			return false;
		}

		// Ignore an entry for the same combination
		if ( $this->getInMemoryParserTracker()->fetch( $this->title->getPrefixedDBKey() ) === $languageCode . '#' . $linkReference ) {
			return '';
		}

		return $this->createErrorMessageFor( 'sil-interlanguagelink-multiplecalls' );
	}

	private function createSiteLanguageLinks( InterlanguageLink $interlanguageLink ) {

		$this->siteLanguageLinksGenerator
			->addLanguageTargetLinksToOutput( $interlanguageLink, $this->title );

		$knownTargetLink = $this->siteLanguageLinksGenerator
			->checkIfTargetIsKnownForCurrentLanguage( $this->title );

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

		$this->getInMemoryParserTracker()->save(
			$this->title->getPrefixedDBKey(),
			$interlanguageLink->getHash()
		);
	}

	private function isSupportedLanguage( $languageCode ) {

		$languageCode = trim( $languageCode );

		if ( strlen( $languageCode ) == 0 ) {
			return false;
		}

		return Language::isSupportedLanguage( $languageCode );
	}

	private function createErrorMessageFor( $messageKey, $arg1 = '', $arg2 = '', $arg3 = '',$arg4 = '' ) {
		return '<span class="error">' . wfMessage( $messageKey, $arg1, $arg2, $arg3, $arg4 )->inContentLanguage()->text() . '</span>';
	}

	private function getInMemoryParserTracker() {

		// Use the FixedInMemoryCache to ensure that during a job run the array is not hit by any
		// memory leak and limited to a fixed size
		if ( self::$inMemoryParserTracker === null ) {
			self::$inMemoryParserTracker = CacheFactory::getInstance()->newFixedInMemoryCache( 50 );
		}

		return self::$inMemoryParserTracker;
	}

}
