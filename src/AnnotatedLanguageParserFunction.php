<?php

namespace SIL;

use MediaWiki\MediaWikiServices;
use SMW\Localizer\Localizer;
use Title;

/**
 * @license GPL-2.0-or-later
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
		$wikitext .= "|lang-name=" . MediaWikiServices::getInstance()->getLanguageNameUtils()->getLanguageName( $languageCode );

		$templateText .= '{{' . $template . $wikitext . '}}';

		return $templateText;
	}

	private function modifyTargetLink( $targetLink ) {
		if ( !$targetLink instanceof Title ) {
			$targetLink = Title::newFromText( $targetLink );
		}

		return ( $targetLink->getNamespace() === NS_CATEGORY ? ':' : '' ) . $targetLink->getPrefixedText();
	}

}
