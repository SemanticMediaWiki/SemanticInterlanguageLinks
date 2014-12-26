<?php

namespace SIL;

use Title;
use ParserOutput;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class SiteLanguageLinksGenerator {

	/**
	 * @var ParserOutput
	 */
	private $parserOutput;

	/**
	 * @var InterlanguageLinksLookup
	 */
	private $interlanguageLinksLookup;

	/**
	 * @var boolean|string|Title
	 */
	private $selectedTargetLinkForCurrentLanguage = false;

	/**
	 * @since 1.0
	 *
	 * @param ParserOutput $parserOutput
	 * @param InterlanguageLinksLookup $interlanguageLinksLookup
	 */
	public function __construct( ParserOutput $parserOutput, InterlanguageLinksLookup $interlanguageLinksLookup ) {
		$this->parserOutput = $parserOutput;
		$this->interlanguageLinksLookup = $interlanguageLinksLookup;
	}

	/**
	 * @since 1.0
	 *
	 * @param  InterlanguageLink $interlanguageLink
	 */
	public function addLanguageTargetLinksToOutput( InterlanguageLink $interlanguageLink ) {

		$languageTargetLinks = $this->interlanguageLinksLookup->tryCachedLanguageTargetLinks( $interlanguageLink );

		if ( is_array( $languageTargetLinks ) ) {
			return $this->addLanguageLinksToOutput(
				$this->sanitizeLanguageTargetLinks( $interlanguageLink, $languageTargetLinks )
			);
		}

		$languageTargetLinks = $this->interlanguageLinksLookup->queryLanguageTargetLinks( $interlanguageLink );

		$this->addLanguageLinksToOutput(
			$this->sanitizeLanguageTargetLinks( $interlanguageLink, $languageTargetLinks )
		);

		$this->doPurgeParserCache( $languageTargetLinks );
	}

	/**
	 * Indentify whether a double assignment did occur by comparing the target for the requested
	 * language and the current article as traget that invoked INTERLANGUAGELINK.
	 *
	 * @since 1.0
	 *
	 * @param Title $target
	 *
	 * @return boolean|string
	 */
	public function checkIfTargetIsKnownForCurrentLanguage( Title $target ) {

		$selectedTargetLinkForCurrentLanguage = $this->selectedTargetLinkForCurrentLanguage;

		if ( $selectedTargetLinkForCurrentLanguage instanceof Title ) {
			 $selectedTargetLinkForCurrentLanguage = $selectedTargetLinkForCurrentLanguage->getPrefixedText();
		}

		if ( $selectedTargetLinkForCurrentLanguage !== $target->getPrefixedText() ) {
			return $selectedTargetLinkForCurrentLanguage;
		}

		return false;
	}

	private function sanitizeLanguageTargetLinks( InterlanguageLink $interlanguageLink, array $languageTargetLinks ) {

		if ( isset( $languageTargetLinks[ $interlanguageLink->getLanguageCode() ] ) ) {
			$this->selectedTargetLinkForCurrentLanguage = $languageTargetLinks[ $interlanguageLink->getLanguageCode() ];
		}

		unset( $languageTargetLinks[ $interlanguageLink->getLanguageCode() ] );
		ksort( $languageTargetLinks );

		return $languageTargetLinks;
	}

	/**
	 * `sil:` is used as internal code to distingish any language link that
	 * was not added by SIL
	 */
	private function addLanguageLinksToOutput( array $languageTargetLinks ) {

		foreach ( $languageTargetLinks as $languageCode => $target ) {

			if ( $target instanceof Title ) {
				$target = $target->getPrefixedText();
			}

			$this->parserOutput->addLanguageLink( 'sil:' . $languageCode . ':' . $target );
		}
	}

	private function doPurgeParserCache( $referencesByLanguageCode ) {
		foreach ( $referencesByLanguageCode as $title ) {
			$title->invalidateCache();
		}
	}

}
