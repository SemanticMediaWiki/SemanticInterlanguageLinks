<?php

namespace SIL;

use MediaWiki\Title\Title;
use SMW\DataItems\Blob;
use SMW\DataItems\Property;
use SMW\DataItems\WikiPage;
use SMW\DataValueFactory;

/**
 * Represents an object for a manual annotation such as [[en:Foo]] where
 * en: is being specified as interwiki
 *
 * @license GPL-2.0-or-later
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
	 * @param Title|string $interwikiLink
	 */
	public function __construct( $interwikiLink ) {
		$this->interwikiLink = $interwikiLink instanceof Title ? $interwikiLink : Title::newFromText( $interwikiLink );
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
		return 'iwl.' . $this->getLanguageCode();
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
		return DataValueFactory::getInstance()->newDataValueByItem(
			new Blob( $this->getLanguageCode() ),
			new Property( PropertyRegistry::SIL_IWL_LANG )
		);
	}

	/**
	 * @since 1.0
	 *
	 * @return DataValue
	 */
	public function newInterwikiReferenceDataValue() {
		return DataValueFactory::getInstance()->newDataValueByItem(
			WikiPage::newFromTitle( $this->getInterwikiReference() ),
			new Property( PropertyRegistry::SIL_IWL_REF )
		);
	}

	/**
	 * @since 1.0
	 *
	 * @return Property
	 */
	public function newContainerProperty() {
		return new Property( PropertyRegistry::SIL_CONTAINER );
	}

}
