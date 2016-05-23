<?php

namespace SIL;

use SMW\ApplicationFactory;
use Parser;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ParserFunctionFactory {

	/**
	 * @since  1.0
	 *
	 * @param InterlanguageLinksLookup $interlanguageLinksLookup
	 *
	 * @return array
	 */
	public function newInterlanguageLinkParserFunctionDefinition( InterlanguageLinksLookup $interlanguageLinksLookup ) {

		$interlanguageLinkParserFunctionDefinition = function( $parser, $languageCode, $linkReference = '' ) use ( $interlanguageLinksLookup ) {

			$parserData = ApplicationFactory::getInstance()->newParserData(
				$parser->getTitle(),
				$parser->getOutput()
			);

			$languageLinkAnnotator = new LanguageLinkAnnotator( $parserData );

			$siteLanguageLinksParserOutputAppender = new SiteLanguageLinksParserOutputAppender(
				$parser->getOutput(),
				$interlanguageLinksLookup
			);

			$interlanguageLinkParserFunction = new InterlanguageLinkParserFunction(
				$parser->getTitle(),
				$languageLinkAnnotator,
				$siteLanguageLinksParserOutputAppender
			);

			$interlanguageLinkParserFunction->setRevisionModeState(
				$GLOBALS['wgRequest']->getVal( 'action' ) !== null || $GLOBALS['wgRequest']->getCheck( 'wpPreview' )
			);

			$interlanguageLinkParserFunction->setInterlanguageLinksHideState(
				$GLOBALS['wgHideInterlanguageLinks']
			);

			return $interlanguageLinkParserFunction->parse( $languageCode, $linkReference );
		};

		return array( 'interlanguagelink', $interlanguageLinkParserFunctionDefinition, Parser::SFH_NO_HASH );
	}

	/**
	 * @since  1.0
	 *
	 * @param InterlanguageLinksLookup $interlanguageLinksLookup
	 *
	 * @return array
	 */
	public function newInterlanguageListParserFunctionDefinition( InterlanguageLinksLookup $interlanguageLinksLookup ) {

		$interlanguageListParserFunctionDefinition = function( $parser, $target, $template = '' ) use ( $interlanguageLinksLookup ) {

			$interlanguageListParserFunction = new InterlanguageListParserFunction(
				$interlanguageLinksLookup
			);

			return $interlanguageListParserFunction->parse( $target, $template );
		};

		return array( 'interlanguagelist', $interlanguageListParserFunctionDefinition, Parser::SFH_NO_HASH );
	}

}
