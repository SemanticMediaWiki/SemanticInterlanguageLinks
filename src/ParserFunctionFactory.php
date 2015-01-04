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
	public function newInterlanguageLinkParserFunction( InterlanguageLinksLookup $interlanguageLinksLookup ) {

		$interlanguageLinkParserFunctionHandler = function( $parser, $languageCode, $linkReference = '' ) use ( $interlanguageLinksLookup ) {

			$parserData = ApplicationFactory::getInstance()->newParserData(
				$parser->getTitle(),
				$parser->getOutput()
			);

			$interlanguageLinkAnnotator = new InterlanguageLinkAnnotator( $parserData );

			$siteLanguageLinksGenerator = new SiteLanguageLinksGenerator(
				$parser->getOutput(),
				$interlanguageLinksLookup
			);

			$interlanguageLinkParserFunction = new InterlanguageLinkParserFunction(
				$parser->getTitle(),
				$interlanguageLinkAnnotator,
				$siteLanguageLinksGenerator
			);

			$interlanguageLinkParserFunction->setInterlanguageLinksState( $GLOBALS['wgHideInterlanguageLinks'] );

			return $interlanguageLinkParserFunction->parse( $languageCode, $linkReference );
		};

		return array( 'interlanguagelink', $interlanguageLinkParserFunctionHandler, Parser::SFH_NO_HASH );
	}

	/**
	 * @since  1.0
	 *
	 * @param InterlanguageLinksLookup $interlanguageLinksLookup
	 *
	 * @return array
	 */
	public function newInterlanguageListParserFunction( InterlanguageLinksLookup $interlanguageLinksLookup ) {

		$interlanguageListParserFunctionHandler = function( $parser, $target, $template = '' ) use ( $interlanguageLinksLookup ) {

			$interlanguageListParserFunction = new InterlanguageListParserFunction(
				$parser,
				$interlanguageLinksLookup
			);

			return $interlanguageListParserFunction->parse( $target, $template );
		};

		return array( 'interlanguagelist', $interlanguageListParserFunctionHandler, Parser::SFH_NO_HASH );
	}

}
