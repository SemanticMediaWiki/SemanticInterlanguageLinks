<?php

use SIL\HookRegistry;
use SMW\ApplicationFactory;

use Onoi\Cache\CacheFactory;

/**
 * @see https://github.com/SemanticMediaWiki/SemanticInterlanguageLinks/
 *
 * @defgroup SIL Semantic Interlanguage Links
 * @codeCoverageIgnore
 */
call_user_func( function () {

	if ( !defined( 'MEDIAWIKI' ) ) {
		die( 'This file is part of the SemanticInterlanguageLinks extension, it is not a valid entry point.' );
	}

	if ( version_compare( $GLOBALS[ 'wgVersion' ], '1.23', 'lt' ) ) {
		die( '<b>Error:</b> This version of <a href="https://github.com/SemanticMediaWiki/SemanticInterlanguageLinks/">SemanticInterlanguageLinks</a> is only compatible with MediaWiki 1.23 or above. You need to upgrade MediaWiki first.' );
	}

	define( 'SIL_VERSION', '1.0-alpha' );

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
	$GLOBALS['wgMessagesDirs']['semanticinterlanguagelinks'] = __DIR__ . '/i18n';
	$GLOBALS['wgExtensionMessagesFiles']['semanticinterlanguagelinks-magic'] = __DIR__ . '/i18n/SemanticInterlanguageLinks.magic.php';

	// Declare property Id constants
	define( 'SIL_PROP_CONTAINER', 'Has interlanguage links' );
	define( 'SIL_PROP_REF', 'Interlanguage reference' );
	define( 'SIL_PROP_LANG', 'Page content language' );

	$GLOBALS['egSILCacheType'] = CACHE_ANYTHING;
	$GLOBALS['egSILUseCategoryFilterByLanguage'] = true;

	// Finalize extension setup
	$GLOBALS['wgExtensionFunctions'][] = function() {

		$cacheFactory = new CacheFactory();

		$compositeCache = $cacheFactory->newCompositeCache( array(
			$cacheFactory->newFixedInMemoryCache( 500 ),
			$cacheFactory->newMediaWikiCache( ObjectCache::getInstance( $GLOBALS['egSILCacheType'] ) )
		) );

		$hookRegistry = new HookRegistry(
			ApplicationFactory::getInstance()->getStore(),
			$compositeCache
		);

		$hookRegistry->register( $GLOBALS['wgHooks'] );
	};

} );
