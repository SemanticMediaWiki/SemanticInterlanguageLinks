<?php

namespace SIL;

use SMW\ApplicationFactory;
use Parser;
use ParserOutput;

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
	 * @param PageContentLanguageOnTheFlyModifier $pageContentLanguageOnTheFlyModifier
	 *
	 * @return array
	 */
	public function newInterlanguageLinkParserFunctionDefinition(
		InterlanguageLinksLookup $interlanguageLinksLookup,
		PageContentLanguageOnTheFlyModifier $pageContentLanguageOnTheFlyModifier
	) {

		$interlanguageLinkParserFunctionDefinition = function(
			Parser $parser, string $languageCode, string $linkReference = ''
		) use ( $interlanguageLinksLookup, $pageContentLanguageOnTheFlyModifier ) {

			$pageContentLanguageDbModifier = new PageContentLanguageDbModifier(
				$parser->getTitle()
			);

			// MW 1.24+
			$pageContentLanguageDbModifier->markAsPageLanguageByDB(
				isset( $GLOBALS['wgPageLanguageUseDB'] ) ? $GLOBALS['wgPageLanguageUseDB'] : false
			);

			$parserOutput = $parser->getOutput() ?? new ParserOutput;

			$parserData = ApplicationFactory::getInstance()->newParserData(
				$parser->getTitle(),
				$parserOutput
			);

			$languageLinkAnnotator = new LanguageLinkAnnotator( $parserData );

			$siteLanguageLinksParserOutputAppender = new SiteLanguageLinksParserOutputAppender(
				$parserOutput,
				$interlanguageLinksLookup
			);

			$interlanguageLinkParserFunction = new InterlanguageLinkParserFunction(
				$parser->getTitle(),
				$languageLinkAnnotator,
				$siteLanguageLinksParserOutputAppender,
				$pageContentLanguageOnTheFlyModifier,
				$pageContentLanguageDbModifier
			);

			$interlanguageLinkParserFunction->setRevisionModeState(
				$GLOBALS['wgRequest']->getVal( 'action' ) === 'edit' ||
				$GLOBALS['wgRequest']->getCheck( 'wpPreview' )
			);

			$interlanguageLinkParserFunction->setInterlanguageLinksHideState(
				$GLOBALS['wgHideInterlanguageLinks']
			);

			return $interlanguageLinkParserFunction->parse( $languageCode, $linkReference );
		};

		return [ 'interlanguagelink', $interlanguageLinkParserFunctionDefinition, Parser::SFH_NO_HASH ];
	}

	/**
	 * @since  1.0
	 *
	 * @param InterlanguageLinksLookup $interlanguageLinksLookup
	 *
	 * @return array
	 */
	public function newInterlanguageListParserFunctionDefinition(
		InterlanguageLinksLookup $interlanguageLinksLookup
	) {

		$interlanguageListParserFunctionDefinition = function(
			Parser $parser, string $target, string $template = ''
		) use ( $interlanguageLinksLookup ) {

			$interlanguageListParserFunction = new InterlanguageListParserFunction(
				$interlanguageLinksLookup
			);

			return $interlanguageListParserFunction->parse( $target, $template );
		};

		return [ 'interlanguagelist', $interlanguageListParserFunctionDefinition, Parser::SFH_NO_HASH ];
	}

	/**
	 * @since  1.4
	 *
	 * @param InterlanguageLinksLookup $interlanguageLinksLookup
	 *
	 * @return array
	 */
	public function newAnnotatedLanguageParserFunctionDefinition(
		InterlanguageLinksLookup $interlanguageLinksLookup
	) {

		$annotatedLanguageParserFunctionDefinition = function(
			Parser $parser, string $template = ''
		) use ( $interlanguageLinksLookup ) {

			$annotatedLanguageParserFunction = new AnnotatedLanguageParserFunction(
				$interlanguageLinksLookup
			);

			return $annotatedLanguageParserFunction->parse( $parser->getTitle(), $template );
		};

		return [ 'annotatedlanguage', $annotatedLanguageParserFunctionDefinition, Parser::SFH_NO_HASH ];
	}

}
