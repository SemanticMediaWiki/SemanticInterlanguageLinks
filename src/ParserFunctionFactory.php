<?php

namespace SIL;

use Parser;
use ParserOutput;
use SMW\Services\ServicesFactory as ApplicationFactory;

/**
 * @license GPL-2.0-or-later
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
		$interlanguageLinkParserFunctionDefinition = function (
			Parser $parser, string $languageCode, string $linkReference = ''
		) use ( $interlanguageLinksLookup, $pageContentLanguageOnTheFlyModifier ) {
			$pageContentLanguageDbModifier = new PageContentLanguageDbModifier(
				$parser->getTitle()
			);

			// MW 1.24+
			$pageContentLanguageDbModifier->markAsPageLanguageByDB(
				isset( $GLOBALS['wgPageLanguageUseDB'] ) ? $GLOBALS['wgPageLanguageUseDB'] : false
			);

			$parserOutput = $this->getParserOutputSafe( $parser ) ?? new ParserOutput;

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
		$interlanguageListParserFunctionDefinition = static function (
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
		$annotatedLanguageParserFunctionDefinition = static function (
			Parser $parser, string $template = ''
		) use ( $interlanguageLinksLookup ) {
			$annotatedLanguageParserFunction = new AnnotatedLanguageParserFunction(
				$interlanguageLinksLookup
			);

			return $annotatedLanguageParserFunction->parse( $parser->getTitle(), $template );
		};

		return [ 'annotatedlanguage', $annotatedLanguageParserFunctionDefinition, Parser::SFH_NO_HASH ];
	}

	private function getParserOutputSafe( ?Parser $parser ): ?ParserOutput {
		try {
			/**
			 * Returns early if the parser has no options, because output
			 * relies on the options.
			 *
			 * @see Parser::resetOutput
			 */
			if ( version_compare( MW_VERSION, '1.42', '>=' ) && $parser->getOptions() === null ) {
				return null;
			}
			return $parser->getOutput();
		} catch ( Error $e ) {
			return null;
		}
	}

}
