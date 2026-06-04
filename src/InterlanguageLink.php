<?php

namespace SIL;

use MediaWiki\Title\Title;
use SMW\DataItems\Blob;
use SMW\DataItems\Property;
use SMW\DataItems\WikiPage;
use SMW\DataValueFactory;

/**
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class InterlanguageLink {

	/**
	 * @var string|null
	 */
	private $languageCode = '';

	/**
	 * @var Title
	 */
	private $linkReference;

	/**
	 * @since 1.0
	 *
	 * @param string|null $languageCode
	 * @param Title|string|null $linkReference
	 */
	public function __construct( $languageCode = null, $linkReference = null ) {
		$this->languageCode = $languageCode;
		$this->linkReference = $linkReference instanceof Title ? $linkReference : Title::newFromText( $linkReference );
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
		return 'ill.' . $this->getLanguageCode();
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
		return DataValueFactory::getInstance()->newDataValueByItem(
			new Blob( $this->getLanguageCode() ),
			new Property( PropertyRegistry::SIL_ILL_LANG )
		);
	}

	/**
	 * @since 1.0
	 *
	 * @return DataValue
	 */
	public function newLinkReferenceDataValue() {
		return DataValueFactory::getInstance()->newDataValueByItem(
			WikiPage::newFromTitle( $this->getLinkReference() ),
			new Property( PropertyRegistry::SIL_ILL_REF )
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
