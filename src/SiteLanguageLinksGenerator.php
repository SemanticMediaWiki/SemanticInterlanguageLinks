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
	 * @param InterlanguageLink $interlanguageLink
	 * @param Title|null $target
	 */
	public function addLanguageTargetLinksToOutput( InterlanguageLink $interlanguageLink, Title $target = null ) {

		$languageTargetLinks = $this->interlanguageLinksLookup->queryLanguageTargetLinks(
			$interlanguageLink,
			$target
		);

		// Always update the cache entry with an annotation directly made to avoid
		// extra lookup during the view action
		$this->interlanguageLinksLookup->updatePageLanguageToLookupCache(
			$target,
			$interlanguageLink->getLanguageCode()
		);

		$this->addLanguageLinksToOutput(
			$interlanguageLink,
			$languageTargetLinks
		);

		$this->doPurgeParserCache( $languageTargetLinks );
	}

	/**
	 * Identify whether a double assignment did occur by comparing the target
	 * for the requested language and the current article as target that invoked
	 * INTERLANGUAGELINK parser.
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

	/**
	 * `sil:` is used as internal code to distinguish any language link that
	 * was not added by SIL
	 */
	private function addLanguageLinksToOutput( InterlanguageLink $interlanguageLink, array $languageTargetLinks ) {

		$languageTargetLinks = $this->sanitizeLanguageTargetLinks(
			$interlanguageLink,
			$languageTargetLinks
		);

		foreach ( $languageTargetLinks as $languageCode => $target ) {

			if ( $target instanceof Title ) {
				$target = $target->getPrefixedText();
			}

			$this->parserOutput->addLanguageLink( 'sil:' . $languageCode . ':' . $target );
		}
	}

	private function sanitizeLanguageTargetLinks( InterlanguageLink $interlanguageLink, array $languageTargetLinks ) {

		if ( isset( $languageTargetLinks[ $interlanguageLink->getLanguageCode() ] ) ) {
			$this->selectedTargetLinkForCurrentLanguage = $languageTargetLinks[ $interlanguageLink->getLanguageCode() ];
		}

		unset( $languageTargetLinks[ $interlanguageLink->getLanguageCode() ] );
		ksort( $languageTargetLinks );

		return $languageTargetLinks;
	}

	private function doPurgeParserCache( $languageTargetLinks ) {
		foreach ( $languageTargetLinks as $languageTargetLink ) {

			if ( !$languageTargetLink instanceof Title ) {
				continue;
			}

			$languageTargetLink->invalidateCache();
		}
	}

}
