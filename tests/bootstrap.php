<?php

if ( php_sapi_name() !== 'cli' ) {
	die( 'Not an entry point' );
}

error_reporting( E_ALL | E_STRICT );
date_default_timezone_set( 'UTC' );
ini_set( 'display_errors', 1 );

$autoloaderClassPath = getenv( "MW_INSTALL_PATH" ) . '/extensions/SemanticMediaWiki/tests/autoloader.php';
if ( !is_readable( $autoloaderClassPath  ) ) {
	die( 'The Semantic MediaWiki test autoloader is not available' );
}

if ( !class_exists( 'SemanticInterlanguageLinks' ) || !defined( 'SIL_VERSION' ) ) {
	die( "\nSemantic Interlanguage Links is not available, please check your Composer or LocalSettings.\n" );
}

if ( !defined( 'SMW_PHPUNIT_FIRST_COLUMN_WIDTH' ) ) {
	define( 'SMW_PHPUNIT_FIRST_COLUMN_WIDTH', 30 );
}

print sprintf( "\n%-27s%s\n", "Semantic Interlanguage Links: ", SIL_VERSION );

$autoloader = require $autoloaderClassPath;
$autoloader->addPsr4( 'SIL\\Tests\\', __DIR__ . '/phpunit/Unit' );
$autoloader->addPsr4( 'SIL\\Tests\\Integration\\', __DIR__ . '/phpunit/Integration' );
unset( $autoloader );
