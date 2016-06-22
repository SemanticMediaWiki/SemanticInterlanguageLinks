<?php

if ( php_sapi_name() !== 'cli' ) {
	die( 'Not an entry point' );
}

error_reporting( E_ALL | E_STRICT );
date_default_timezone_set( 'UTC' );
ini_set( 'display_errors', 1 );

if ( !is_readable( $autoloaderClassPath = __DIR__ . '/../../SemanticMediaWiki/tests/autoloader.php' ) ) {
	die( 'The Semantic MediaWiki test autoloader is not available' );
}

if ( !class_exists( 'SemanticInterlanguageLinks' ) || ( $version = SemanticInterlanguageLinks::getVersion() ) === null ) {
	die( "\nSemantic Interlanguage Links is not available, please check your Composer or LocalSettings.\n" );
}

print sprintf( "\n%-27s%s\n", "Semantic Interlanguage Links: ", $version );

$autoloader = require $autoloaderClassPath;
$autoloader->addPsr4( 'SIL\\Tests\\', __DIR__ . '/phpunit/Unit' );
$autoloader->addPsr4( 'SIL\\Tests\\Integration\\', __DIR__ . '/phpunit/Integration' );
unset( $autoloader );
