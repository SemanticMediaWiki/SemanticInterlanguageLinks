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

	const SIL_ILL_LANG  = '__sil_ill_lang';
	const SIL_ILL_REF   = '__sil_ill_ref';
	const SIL_ILL_CONTAINER = '__sil_ill_container';

	/**
	 * @since 1.0
	 *
	 * @return boolean
	 */
	public function register() {

		$propertyDefinitions = array(

			self::SIL_ILL_LANG => array(
				'label' => SIL_PROP_ILL_LANG,
				'type'  => '_txt',
				'alias' => wfMessage( 'sil-property-ill-alias-language' )->text(),
				'visibility' => true
			),

			self::SIL_ILL_REF => array(
				'label' => SIL_PROP_ILL_REF,
				'type'  => '_wpg',
				'alias' => wfMessage( 'sil-property-ill-alias-reference' )->text(),
				'visibility' => true
			),

			self::SIL_ILL_CONTAINER => array(
				'label' => SIL_PROP_ILL_CONTAINER,
				'type'  => '__sob',
				'alias' => wfMessage( 'sil-property-ill-alias-container' )->text(),
				'visibility' => false
			),
		);

		foreach ( $propertyDefinitions as $propertyId => $definition ) {

			DIProperty::registerProperty(
				$propertyId,
				$definition['type'],
				$definition['label'],
				$definition['visibility']
			);

			DIProperty::registerPropertyAlias(
				$propertyId,
				$definition['alias']
			);
		}

		return true;
	}

}
