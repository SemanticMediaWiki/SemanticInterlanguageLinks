<?php

namespace SIL\Hooks;

use SIL\PropertyRegistry;

/**
 * Excludes the SIL language properties from Semantic MediaWiki's fulltext
 * search index during settings initialization.
 *
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */
class ConfigHooks {

	/**
	 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::Settings::BeforeInitializationComplete
	 */
	public function onSMW__Settings__BeforeInitializationComplete( &$config ): void {
		if ( !isset( $config['smwgFulltextSearchPropertyExemptionList'] ) ) {
			return;
		}

		// Exclude those properties from indexing
		$config['smwgFulltextSearchPropertyExemptionList'] = array_merge(
			$config['smwgFulltextSearchPropertyExemptionList'],
			[ PropertyRegistry::SIL_IWL_LANG, PropertyRegistry::SIL_ILL_LANG ]
		);
	}

}
