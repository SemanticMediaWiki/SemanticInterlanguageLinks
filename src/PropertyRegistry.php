<?php

namespace SIL;

use SMW\DIProperty;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class PropertyRegistry {

	const SIL_LANG  = '__sil_lang';
	const SIL_REF   = '__sil_ref';
	const SIL_CONTAINER = '__sil_container';

	/**
	 * @since 1.0
	 *
	 * @return boolean
	 */
	public static function register() {

		$propertyDefinitions = array(

			self::SIL_LANG => array(
				'label' => SIL_PROP_LANG,
				'type'  => '_txt',
				'alias' => wfMessage( 'sil-property-alias-language' )->text()
			),

			self::SIL_REF => array(
				'label' => SIL_PROP_REF,
				'type'  => '_wpg',
				'alias' => wfMessage( 'sil-property-alias-reference' )->text()
			),

			self::SIL_CONTAINER => array(
				'label' => SIL_PROP_CONTAINER,
				'type'  => '__sob',
				'alias' => wfMessage( 'sil-property-alias-container' )->text()
			),
		);

		foreach ( $propertyDefinitions as $propertyId => $definition ) {

			DIProperty::registerProperty(
				$propertyId,
				$definition['type'],
				$definition['label'],
				true
			);

			DIProperty::registerPropertyAlias(
				$propertyId,
				$definition['alias']
			);
		}

		return true;
	}

}
