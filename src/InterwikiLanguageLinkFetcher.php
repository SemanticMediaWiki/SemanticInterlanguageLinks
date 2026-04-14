<?php

namespace SIL;

use MediaWiki\Title\Title;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Parser\ParserOutputLinkTypes;

/**
 * @license GPL-2.0-or-later
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

		foreach ( $parserOutput->getLinkList( ParserOutputLinkTypes::LANGUAGE ) as $languageLink ) {

			if ( !$languageLink || !$languageLink['link'] ) {
				continue;
			}

			$languageLink = Title::castFromLinkTarget( $languageLink['link'] );
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
