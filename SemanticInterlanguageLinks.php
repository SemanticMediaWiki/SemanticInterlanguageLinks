<?php

use SIL\HookRegistry;
use SIL\CacheKeyProvider;
use SMW\ApplicationFactory;
use Onoi\Cache\CacheFactory;

/**
 * @see https://github.com/SemanticMediaWiki/SemanticInterlanguageLinks/
 *
 * @defgroup SIL Semantic Interlanguage Links
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of the SemanticInterlanguageLinks extension, it is not a valid entry point.' );
}

if ( version_compare( $GLOBALS[ 'wgVersion' ], '1.23', 'lt' ) ) {
	die( '<b>Error:</b> This version of <a href="https://github.com/SemanticMediaWiki/SemanticInterlanguageLinks/">SemanticInterlanguageLinks</a> is only compatible with MediaWiki 1.23 or above. You need to upgrade MediaWiki first.' );
}

if ( defined( 'SIL_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

define( 'SIL_VERSION', '1.2.0' );

/**
 * @codeCoverageIgnore
 */
call_user_func( function () {

	// Register extension info
	$GLOBALS[ 'wgExtensionCredits' ][ 'semantic' ][ ] = array(
		'path'           => __FILE__,
		'name'           => 'Semantic Interlanguage Links',
		'author'         => array( 'James Hong Kong' ),
		'url'            => 'https://github.com/SemanticMediaWiki/SemanticInterlanguageLinks/',
		'descriptionmsg' => 'sil-desc',
		'version'        => SIL_VERSION,
		'license-name'   => 'GPL-2.0+',
	);

	// Register message files
	$GLOBALS['wgMessagesDirs']['semantic-interlanguage-links'] = __DIR__ . '/i18n';
	$GLOBALS['wgExtensionMessagesFiles']['semantic-interlanguage-links-magic'] = __DIR__ . '/i18n/SemanticInterlanguageLinks.magic.php';

	$GLOBALS['egSILCacheType'] = CACHE_ANYTHING;
	$GLOBALS['egSILEnabledCategoryFilterByLanguage'] = true;

	// Finalize extension setup
	$GLOBALS['wgExtensionFunctions'][] = function() {

		$cacheFactory = new CacheFactory();

		$compositeCache = $cacheFactory->newCompositeCache( array(
			$cacheFactory->newFixedInMemoryLruCache( 500 ),
			$cacheFactory->newMediaWikiCache( ObjectCache::getInstance( $GLOBALS['egSILCacheType'] ) )
		) );

		$cacheKeyProvider = new CacheKeyProvider(
			$GLOBALS['wgCachePrefix'] === false ? wfWikiID() : $GLOBALS['wgCachePrefix']
		);

		$hookRegistry = new HookRegistry(
			ApplicationFactory::getInstance()->getStore(),
			$compositeCache,
			$cacheKeyProvider
		);

		$hookRegistry->register();
	};

} );
