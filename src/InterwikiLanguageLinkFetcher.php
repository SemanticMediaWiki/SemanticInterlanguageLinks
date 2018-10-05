<?php

namespace SIL;

use ParserOutput;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class InterwikiLanguageLinkFetcher {

	/**
	 * @var LanguageLinkAnnotator
	 */
	private $languageLinkAnnotator;

	/**
	 * @since 1.0
	 *
	 * @param LanguageLinkAnnotator $languageLinkAnnotator
	 */
	public function __construct( LanguageLinkAnnotator $languageLinkAnnotator ) {
		$this->languageLinkAnnotator = $languageLinkAnnotator;
	}

	/**
	 * @since 1.0
	 *
	 * @return ParserOutput $parserOutput
	 */
	public function fetchLanguagelinksFromParserOutput( ParserOutput $parserOutput ) {

		if ( $parserOutput->getLanguageLinks() === [] || $parserOutput->getLanguageLinks() === null ) {
			return;
		}

		foreach ( $parserOutput->getLanguageLinks() as $languageLink ) {

			if ( strpos( $languageLink, 'sil:' ) !== false ) {
				continue;
			}

			$this->addAnnotationForInterwikiLanguageLink( $languageLink );
		}
	}

	private function addAnnotationForInterwikiLanguageLink( $languageLink ) {

		$interwikiLanguageLink = new InterwikiLanguageLink( $languageLink );

		if ( $interwikiLanguageLink->getLanguageCode() === '' ) {
			return;
		}

		$this->languageLinkAnnotator->addAnnotationForInterwikiLanguageLink(
			$interwikiLanguageLink
		);
	}

}
