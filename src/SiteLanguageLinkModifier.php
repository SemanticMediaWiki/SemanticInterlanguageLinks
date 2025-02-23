<?php

namespace SIL;

use MediaWiki\MediaWikiServices;
use Title;

/**
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class SiteLanguageLinkModifier {

	/**
	 * @var Title
	 */
	private $titleForExternalLanguageLink;

	/**
	 * @var Title
	 */
	private $titleToTargetLink;

	/**
	 * @since 1.0
	 *
	 * @param Title $titleForExternalLanguageLink
	 * @param Title $titleToTargetLink
	 */
	public function __construct( Title $titleForExternalLanguageLink, Title $titleToTargetLink ) {
		$this->titleForExternalLanguageLink = $titleForExternalLanguageLink;
		$this->titleToTargetLink = $titleToTargetLink;
	}

	/**
	 * @since 1.0
	 *
	 * @param array &$languageLink
	 *
	 * @return bool
	 */
	public function modifyLanguageLink( &$languageLink ) {
		if ( !isset( $languageLink['text'] ) || strpos( $languageLink['text'], 'sil:' ) === false ) {
			return false;
		}

		[ $internalId, $languageCode, $target ] = explode( ':', $languageLink['text'], 3 );

		if ( $internalId !== 'sil' ) {
			return false;
		}

		$languageName = MediaWikiServices::getInstance()->getLanguageNameUtils()->getLanguageName( $languageCode );

		$languageLink = [
			'href'  => Title::newFromText( $target )->getFullURL(),
			'text'  => $languageName,
			'title' => $languageName,
			'class' => '',
			'lang'  => $languageCode,
			'hreflang' => $languageCode,
		];

		return true;
	}

}
