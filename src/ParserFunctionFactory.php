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
	 * @return boolean
	 */
	public function newInterlanguageLinkParserFunction( InterlanguageLinksLookup $interlanguageLinksLookup ) {

		$interlanguageLinkParserFunctionHandler = function( $parser, $text, $uarg = '' ) use ( $interlanguageLinksLookup ) {

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

			return $interlanguageLinkParserFunction->parse( $text, $uarg );
		};

		return array( 'interlanguagelink', $interlanguageLinkParserFunctionHandler, Parser::SFH_NO_HASH );
	}

}
