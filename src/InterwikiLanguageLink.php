<?php

namespace SIL;

use SMW\DataValueFactory;
use SMW\DIProperty;
use SMW\DIWikiPage;

use SMWDIBlob as DIBlob;

use Title;

/**
 * Represents an object for a manual annotation such as [[en:Foo]] where
 * en: is being specified as interwiki
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class InterwikiLanguageLink {

	/**
	 * @var Title
	 */
	private $interwikiLink;

	/**
	 * @since 1.0
	 *
	 * @param string $interwikiLink
	 */
	public function __construct( $interwikiLink ) {
		$this->interwikiLink = $interwikiLink instanceOf Title ? $interwikiLink : Title::newFromText( $interwikiLink );
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public function getLanguageCode() {
		return $this->interwikiLink->getInterwiki();
	}

	/**
	 * @since 1.0
	 *
	 * @return Title
	 */
	public function getInterwikiReference() {
		return $this->interwikiLink;
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public function getContainerId() {
		return 'iwl.'. $this->getLanguageCode();
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public function getHash() {
		return $this->getLanguageCode() . '#' . $this->getInterwikiReference()->getPrefixedText();
	}

	/**
	 * @since 1.0
	 *
	 * @return DataValue
	 */
	public function newLanguageDataValue() {
		return DataValueFactory::getInstance()->newDataItemValue(
			new DIBlob( $this->getLanguageCode() ),
			new DIProperty( PropertyRegistry::SIL_IWL_LANG )
		);
	}

	/**
	 * @since 1.0
	 *
	 * @return DataValue
	 */
	public function newInterwikiReferenceDataValue() {
		return DataValueFactory::getInstance()->newDataItemValue(
			DIWikiPage::newFromTitle( $this->getInterwikiReference() ),
			new DIProperty( PropertyRegistry::SIL_IWL_REF )
		);
	}

	/**
	 * @since 1.0
	 *
	 * @return DIProperty
	 */
	public function newContainerProperty() {
		return new DIProperty( PropertyRegistry::SIL_CONTAINER );
	}

}
