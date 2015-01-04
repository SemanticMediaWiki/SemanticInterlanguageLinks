<?php

namespace SIL;

use SMW\Store;
use SMW\DIWikiPage;
use SMW\DIProperty;

use Title;

/**
 * Modifies the content language based on the SIL annotation found
 * for the selected page.
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class PageContentLanguageModifier {

	/**
	 * @var InterlanguageLinksLookup
	 */
	private $interlanguageLinksLookup;

	/**
	 * @var Title
	 */
	private $title;

	/**
	 * @since 1.0
	 *
	 * @param InterlanguageLinksLookup $interlanguageLinksLookup
	 * @param Title $title
	 */
	public function __construct( InterlanguageLinksLookup $interlanguageLinksLookup, Title $title ) {
		$this->interlanguageLinksLookup = $interlanguageLinksLookup;
		$this->title = $title;
	}

	/**
	 * @since 1.0
	 *
	 * @param Language|string &$pageLanguage
	 *
	 * @return boolean
	 */
	public function modifyLanguage( &$pageLanguage ) {

		$lookupLanguageCode = $this->interlanguageLinksLookup->findPageLanguageForTarget( $this->title );

		if ( $lookupLanguageCode !== '' ) {
			$pageLanguage = $lookupLanguageCode;
		}

		return true;
	}

}
