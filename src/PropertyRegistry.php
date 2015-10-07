<?php

namespace SIL;

use SMW\DIProperty;

define( 'SIL_PROP_CONTAINER', 'Has interlanguage link' );

define( 'SIL_PROP_ILL_REF', 'Interlanguage reference' );
define( 'SIL_PROP_ILL_LANG', 'Page content language' );

define( 'SIL_PROP_IWL_REF', 'Interwiki reference' );
define( 'SIL_PROP_IWL_LANG', 'Interwiki language' );

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
				'alias' => array( wfMessage( 'sil-property-alias-container' )->text(), 'hasInterlanguagelink' ),
				'visibility' => false,
				'annotableByUser'  => false
			),

			self::SIL_ILL_LANG => array(
				'label' => SIL_PROP_ILL_LANG,
				'type'  => '_txt',
				'alias' => array( wfMessage( 'sil-property-ill-alias-language' )->text() ),
				'visibility' => true,
				'annotableByUser'  => true
			),

			self::SIL_ILL_REF => array(
				'label' => SIL_PROP_ILL_REF,
				'type'  => '_wpg',
				'alias' => array( wfMessage( 'sil-property-ill-alias-reference' )->text() ),
				'visibility' => true,
				'annotableByUser'  => false
			),

			self::SIL_IWL_LANG => array(
				'label' => SIL_PROP_IWL_LANG,
				'type'  => '_txt',
				'alias' => array( wfMessage( 'sil-property-iwl-alias-language' )->text() ),
				'visibility' => true,
				'annotableByUser'  => false
			),

			self::SIL_IWL_REF => array(
				'label' => SIL_PROP_IWL_REF,
				'type'  => '_wpg',
				'alias' => array( wfMessage( 'sil-property-iwl-alias-reference' )->text() ),
				'visibility' => true,
				'annotableByUser'  => false
			)
		);

		foreach ( $propertyDefinitions as $propertyId => $definition ) {
			$this->addPropertyDefinitionFor( $propertyId, $definition  );
		}

		return true;
	}

	private function addPropertyDefinitionFor( $propertyId, $definition ) {

		DIProperty::registerProperty(
			$propertyId,
			$definition['type'],
			$definition['label'],
			$definition['visibility'],
			$definition['annotableByUser']
		);

		foreach ( $definition['alias'] as $alias ) {
			DIProperty::registerPropertyAlias(
				$propertyId,
				$alias
			);
		}
	}

}
