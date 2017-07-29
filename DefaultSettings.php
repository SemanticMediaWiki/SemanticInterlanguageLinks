<?php

/**
 * DO NOT EDIT!
 *
 * The following default settings are to be used by the extension itself,
 * please modify settings in the LocalSettings file.
 *
 * @codeCoverageIgnore
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of the SemanticInterlanguageLinks extension, it is not a valid entry point.' );
}

/**
 * Cache used to improve query lookups
 */
$GLOBALS['silgCacheType'] = CACHE_ANYTHING;

/**
 * Enable language filtering on the category page
 */
$GLOBALS['silgEnabledCategoryFilterByLanguage'] = true;
