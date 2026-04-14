<?php

namespace SIL;

use MediaWiki\Parser\ParserOutput;
use MediaWiki\Parser\ParserOutputLinkTypes;
use MediaWiki\Title\Title;

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

			$languageLink = $languageLink['link'] ?? '';
			if ( !$languageLink ) {
				continue;
			}

			$languageLink = Title::makeTitleSafe(
				$languageLink->getNamespace(),
				$languageLink->getText(),
				$languageLink->getFragment(),
				$languageLink->getInterwiki()
			);
			print_r( 'hello' );
			print_r( $languageLink );
			print_r( strpos( $languageLink, 'sil:' ) );
			if ( $languageLink === null || strpos( $languageLink, 'sil:' ) !== false ) {
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
