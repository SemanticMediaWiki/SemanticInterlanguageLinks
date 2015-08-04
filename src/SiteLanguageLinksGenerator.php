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
	 *
	 * @return string
	 */
	public function tryAddLanguageTargetLinksToOutput( InterlanguageLink $interlanguageLink, Title $target = null ) {

		$knownTargetLink = '';
		$selectedTargetLinkForCurrentLanguage = false;

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

		if ( isset( $languageTargetLinks[ $interlanguageLink->getLanguageCode() ] ) ) {
			$selectedTargetLinkForCurrentLanguage = $languageTargetLinks[ $interlanguageLink->getLanguageCode() ];
		}

		$knownTargetLink = $this->compareTargetToCurrentLanguage(
			$target,
			$selectedTargetLinkForCurrentLanguage
		);

		if ( !$knownTargetLink ) {
			$this->addLanguageLinksToOutput(
				$interlanguageLink,
				$languageTargetLinks
			);
		}

		$this->doPurgeParserCache( $languageTargetLinks );

		return $knownTargetLink;
	}

	/**
	 * Identify whether a double assignment did occur by comparing the target
	 * for the requested language and the current article as target that invoked
	 * INTERLANGUAGELINK parser.
	 *
	 * @return boolean|string
	 */
	private function compareTargetToCurrentLanguage( Title $target = null, $selectedTargetLinkForCurrentLanguage ) {

		if ( $selectedTargetLinkForCurrentLanguage instanceof Title ) {
			 $selectedTargetLinkForCurrentLanguage = $selectedTargetLinkForCurrentLanguage->getPrefixedText();
		}

		if ( $target !== null && $selectedTargetLinkForCurrentLanguage !== $target->getPrefixedText() ) {

			$title = Title::newFromText( $selectedTargetLinkForCurrentLanguage );

			if ( $title->isRedirect() ) {
				 $this->interlanguageLinksLookup->invalidateLookupCache( $target );
				 return false;
			}

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
