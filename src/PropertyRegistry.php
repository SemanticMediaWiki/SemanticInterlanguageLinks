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

	const SIL_CONTAINER = '__sil_container';

	const SIL_ILL_LANG  = '__sil_ill_lang';
	const SIL_ILL_REF   = '__sil_ill_ref';

	const SIL_IWL_LANG  = '__sil_iwl_lang';
	const SIL_IWL_REF   = '__sil_iwl_ref';

	/**
	 * @since 1.0
	 *
	 * @return boolean
	 */
	public function register() {

		$propertyDefinitions = array(

			self::SIL_CONTAINER => array(
				'label' => SIL_PROP_CONTAINER,
				'type'  => '__sob',
				'alias' => wfMessage( 'sil-property-alias-container' )->text(),
				'visibility' => false
			),

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

			self::SIL_IWL_LANG => array(
				'label' => SIL_PROP_IWL_LANG,
				'type'  => '_txt',
				'alias' => wfMessage( 'sil-property-iwl-alias-language' )->text(),
				'visibility' => true
			),

			self::SIL_IWL_REF => array(
				'label' => SIL_PROP_IWL_REF,
				'type'  => '_wpg',
				'alias' => wfMessage( 'sil-property-iwl-alias-reference' )->text(),
				'visibility' => true
			)
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
