<?php

if ( php_sapi_name() !== 'cli' ) {
	die( 'Not an entry point' );
}

if ( !is_readable( $autoloaderClassPath = __DIR__ . '/../../SemanticMediaWiki/tests/autoloader.php' ) ) {
	die( 'The SemanticMediaWiki test autoloader is not available' );
}

print sprintf( "\n%-27s%s\n", "Semantic Interlanguage Links: ", SIL_VERSION );

$autoloader = require $autoloaderClassPath;
$autoloader->addPsr4( 'SIL\\Tests\\', __DIR__ . '/phpunit/Unit' );
$autoloader->addPsr4( 'SIL\\Tests\\Integration\\', __DIR__ . '/phpunit/Integration' );

