<?php

namespace SIL;

use Title;
use Language;
use Parser;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class InterlanguageListParserFunction {

	/**
	 * @var Parser
	 */
	private $parser;

	/**
	 * @var InterlanguageLinksLookup
	 */
	private $interlanguageLinksLookup;

	/**
	 * @since 1.0
	 *
	 * @param Parser $parser
	 * @param InterlanguageLinksLookup $interlanguageLinksLookup
	 */
	public function __construct( Parser $parser, InterlanguageLinksLookup $interlanguageLinksLookup ) {
		$this->parser = $parser;
		$this->interlanguageLinksLookup = $interlanguageLinksLookup;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $linkReference
	 * @param string $template
	 *
	 * @return string
	 */
	public function parse( $linkReference, $template ) {

		if ( $linkReference === '' ) {
			return $this->createErrorMessageFor( 'sil-interlanguagelist-missing-linkreference' );
		}

		if ( $template === '' ) {
			return $this->createErrorMessageFor( 'sil-interlanguagelist-missing-template' );
		}

		$title = Title::newFromText( $linkReference );

		if ( $title === null ) {
			return $this->createErrorMessageFor( 'sil-interlanguageparser-linkreference-error', $linkReference );
		}

		// `null` indicates to the lookup interface to return all matches regardless
		// of any language code
		$interlanguageLink = new InterlanguageLink(
			null,
			$title
		);

		$languageTargetLinks = $this->getLanguageTargetLinks( $interlanguageLink );

		return $this->createTemplateInclusionCode( $languageTargetLinks, $template );
	}

	private function getLanguageTargetLinks( InterlanguageLink $interlanguageLink ) {

		$languageTargetLinks = $this->interlanguageLinksLookup->queryLanguageTargetLinks( $interlanguageLink );

		ksort( $languageTargetLinks );

		return $languageTargetLinks;
	}

	private function createTemplateInclusionCode( array $languageTargetLinks, $template ) {

		$result = '';
		$templateText = '';
		$i = 0;

		foreach ( $languageTargetLinks as $languageCode => $targetLink ) {

			$wikitext = '';

			$wikitext .= "|list-pos=" . $i++;
			$wikitext .= "|target-link=" . $targetLink;
			$wikitext .= "|lang-code=" . $languageCode;
			$wikitext .= "|lang-name=" . Language::fetchLanguageName( $languageCode );

			$templateText .= '{{' . $template . $wikitext . '}}';

		}

		if ( $templateText !== '' ) {
			$result = array( $this->parser->recursiveTagParse( $templateText ), 'isHTML' => true );
		}

		return $result;
	}

	private function createErrorMessageFor( $messageKey, $arg1 = '' ) {
		return '<span class="error">' . wfMessage( $messageKey, $arg1 )->inContentLanguage()->text() . '</span>';
	}

}
