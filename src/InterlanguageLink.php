<?php

namespace SIL;

use SMW\DataValueFactory;
use SMW\DIProperty;
use SMW\DIWikiPage;

use SMWDIBlob as DIBlob;

use Title;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class InterlanguageLink {

	/**
	 * @var string
	 */
	private $languageCode;

	/**
	 * @var Title
	 */
	private $linkReference;

	/**
	 * @since 1.0
	 *
	 * @param string $languageCode
	 * @param string $linkReference
	 */
	public function __construct( $languageCode, $linkReference ) {
		$this->languageCode = $languageCode;
		$this->linkReference = Title::newFromText( $linkReference );
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public function getLanguageCode() {
		return $this->languageCode;
	}

	/**
	 * @since 1.0
	 *
	 * @return Title
	 */
	public function getLinkReference() {
		return $this->linkReference;
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public function getContainerId() {
		return 'sil.'. $this->getLanguageCode();
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public function getHash() {
		return $this->getLanguageCode() . '#' . $this->getLinkReference()->getPrefixedText();
	}

	/**
	 * @since 1.0
	 *
	 * @return DataValue
	 */
	public function newLanguageDataValue() {
		return DataValueFactory::getInstance()->newDataItemValue(
			new DIBlob( $this->getLanguageCode() ),
			new DIProperty( PropertyRegistry::SIL_LANG )
		);
	}

	/**
	 * @since 1.0
	 *
	 * @return DataValue
	 */
	public function newLinkReferenceDataValue() {
		return DataValueFactory::getInstance()->newDataItemValue(
			DIWikiPage::newFromTitle( $this->getLinkReference() ),
			new DIProperty( PropertyRegistry::SIL_REF )
		);
	}

	/**
	 * @since 1.0
	 *
	 * @return DIProperty
	 */
	public function newInterlanguageLinkContainerProperty() {
		return new DIProperty( PropertyRegistry::SIL_CONTAINER );
	}

}
