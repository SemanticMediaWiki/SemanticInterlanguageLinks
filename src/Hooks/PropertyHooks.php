<?php

namespace SIL\Hooks;

use SIL\PropertyRegistry;

/**
 * Registers the SIL properties with Semantic MediaWiki.
 *
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */
class PropertyHooks {

	/**
	 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::Property::initProperties
	 */
	public function onSMW__Property__initProperties( $baseRegistry ): bool {
		$propertyRegistry = new PropertyRegistry();

		$propertyRegistry->register(
			$baseRegistry
		);

		return true;
	}

}
