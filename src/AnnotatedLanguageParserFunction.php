<?php

namespace SIL;

use Title;
use Language;

use SMW\Localizer;

/**
 * @license GNU GPL v2+
 * @since 1.4
 *
 * @author mwjames
 */
class AnnotatedLanguageParserFunction {

	/**
	 * @var InterlanguageLinksLookup
	 */
	private $interlanguageLinksLookup;

	/**
	 * @since 1.0
	 *
	 * @param InterlanguageLinksLookup $interlanguageLinksLookup
	 */
	public function __construct( InterlanguageLinksLookup $interlanguageLinksLookup ) {
		$this->interlanguageLinksLookup = $interlanguageLinksLookup;
	}

	/**
	 * @since 1.0
	 *
	 * @param Title $source
	 * @param string $template
	 *
	 * @return string
	 */
	public function parse( Title $source, $template ) {

		$source = $this->interlanguageLinksLookup->getRedirectTargetFor( $source );

		if ( $source === null ) {
			return '';
		}

		$languageCode = $this->interlanguageLinksLookup->findPageLanguageForTarget( $source );

		if ( $languageCode === '' ) {
			return '';
		}

		if ( $template === '' ) {
			return $languageCode;
		}

		$templateText = $this->createTemplateInclusionCode(
			$source,
			$languageCode,
			$template
		);

		return [ $templateText, 'noparse' => $templateText === '', 'isHTML' => false ];
	}

	private function createTemplateInclusionCode( $source, $languageCode, $template ) {

		$result = '';
		$templateText = '';
		$wikitext = '';

		$wikitext .= "|target-link=" . $this->modifyTargetLink( $source );
		$wikitext .= "|lang-code=" . Localizer::asBCP47FormattedLanguageCode( $languageCode );
		$wikitext .= "|lang-name=" . Language::fetchLanguageName( $languageCode );

		$templateText .= '{{' . $template . $wikitext . '}}';

		return $templateText;
	}

	private function modifyTargetLink( $targetLink ) {

		if ( !$targetLink instanceOf Title ) {
			$targetLink = Title::newFromText( $targetLink );
		}

		return ( $targetLink->getNamespace() === NS_CATEGORY ? ':' : '' ) . $targetLink->getPrefixedText();
	}

}
