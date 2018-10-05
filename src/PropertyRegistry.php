<?php

namespace SIL;

use SMW\PropertyRegistry as CorePropertyRegistry;

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
	 * @param CorePropertyRegistry $propertyRegistry
	 *
	 * @return boolean
	 */
	public function register( CorePropertyRegistry $propertyRegistry ) {

		$propertyDefinitions = [

			self::SIL_CONTAINER => [
				'label' => SIL_PROP_CONTAINER,
				'type'  => '__sob',
				'alias' => [ wfMessage( 'sil-property-alias-container' )->text(), 'hasInterlanguagelink', 'Has interlanguage links' ],
				'msgkey' => 'sil-property-alias-container',
				'visibility' => false,
				'annotableByUser'  => false
			],

			self::SIL_ILL_LANG => [
				'label' => SIL_PROP_ILL_LANG,
				'type'  => '_txt',
				'alias' => [ wfMessage( 'sil-property-ill-alias-language' )->text() ],
				'msgkey' => 'sil-property-ill-alias-language',
				'visibility' => true,
				'annotableByUser'  => true
			],

			self::SIL_ILL_REF => [
				'label' => SIL_PROP_ILL_REF,
				'type'  => '_wpg',
				'alias' => [ wfMessage( 'sil-property-ill-alias-reference' )->text() ],
				'msgkey' => 'sil-property-ill-alias-reference',
				'visibility' => true,
				'annotableByUser'  => false
			],

			self::SIL_IWL_LANG => [
				'label' => SIL_PROP_IWL_LANG,
				'type'  => '_txt',
				'alias' => [ wfMessage( 'sil-property-iwl-alias-language' )->text() ],
				'msgkey' => 'sil-property-iwl-alias-language',
				'visibility' => true,
				'annotableByUser'  => false
			],

			self::SIL_IWL_REF => [
				'label' => SIL_PROP_IWL_REF,
				'type'  => '_wpg',
				'alias' => [ wfMessage( 'sil-property-iwl-alias-reference' )->text() ],
				'msgkey' => 'sil-property-iwl-alias-reference',
				'visibility' => true,
				'annotableByUser'  => false
			]
		];

		foreach ( $propertyDefinitions as $propertyId => $definition ) {
			$this->addPropertyDefinitionFor( $propertyRegistry, $propertyId, $definition  );
		}

		foreach ( $propertyDefinitions as $propertyId => $definition ) {
			// 2.4+
			if ( method_exists( $propertyRegistry, 'registerPropertyAliasByMsgKey' ) ) {
				$propertyRegistry->registerPropertyAliasByMsgKey(
					$propertyId,
					$definition['msgkey']
				);
			}
		}

		return true;
	}

	private function addPropertyDefinitionFor( $propertyRegistry, $propertyId, $definition ) {

		$propertyRegistry->registerProperty(
			$propertyId,
			$definition['type'],
			$definition['label'],
			$definition['visibility'],
			$definition['annotableByUser']
		);

		foreach ( $definition['alias'] as $alias ) {
			$propertyRegistry->registerPropertyAlias(
				$propertyId,
				$alias
			);
		}
	}

}
