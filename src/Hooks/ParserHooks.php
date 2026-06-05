<?php

namespace SIL\Hooks;

use MediaWiki\Hook\ParserAfterTidyHook;
use MediaWiki\Hook\ParserFirstCallInitHook;
use SIL\InterlanguageLinksLookup;
use SIL\InterwikiLanguageLinkFetcher;
use SIL\LanguageLinkAnnotator;
use SIL\PageContentLanguageOnTheFlyModifier;
use SIL\ParserFunctionFactory;
use SMW\Services\ServicesFactory as ApplicationFactory;

/**
 * Registers the interlanguage parser functions and turns interwiki language
 * links found in the parser output into SIL annotations.
 *
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */
class ParserHooks implements ParserFirstCallInitHook, ParserAfterTidyHook {

	public function __construct(
		private readonly InterlanguageLinksLookup $interlanguageLinksLookup,
		private readonly PageContentLanguageOnTheFlyModifier $pageContentLanguageOnTheFlyModifier
	) {
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
	 */
	public function onParserFirstCallInit( $parser ): bool {
		$parserFunctionFactory = new ParserFunctionFactory();

		[ $name, $definition, $flag ] = $parserFunctionFactory->newInterlanguageLinkParserFunctionDefinition(
			$this->interlanguageLinksLookup,
			$this->pageContentLanguageOnTheFlyModifier
		);

		$parser->setFunctionHook( $name, $definition, $flag );

		[ $name, $definition, $flag ] = $parserFunctionFactory->newInterlanguageListParserFunctionDefinition(
			$this->interlanguageLinksLookup
		);

		$parser->setFunctionHook( $name, $definition, $flag );

		[ $name, $definition, $flag ] = $parserFunctionFactory->newAnnotatedLanguageParserFunctionDefinition(
			$this->interlanguageLinksLookup
		);

		$parser->setFunctionHook( $name, $definition, $flag );

		return true;
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserAfterTidy
	 */
	public function onParserAfterTidy( $parser, &$text ): bool {
		$parserData = ApplicationFactory::getInstance()->newParserData(
			$parser->getTitle(),
			$parser->getOutput()
		);

		$languageLinkAnnotator = new LanguageLinkAnnotator( $parserData );
		$interwikiLanguageLinkFetcher = new InterwikiLanguageLinkFetcher( $languageLinkAnnotator );

		$interwikiLanguageLinkFetcher->fetchLanguagelinksFromParserOutput( $parser->getOutput() );

		return true;
	}

}
