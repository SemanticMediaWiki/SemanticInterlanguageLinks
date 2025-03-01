<?php

namespace SIL\Tests\Integration;

use SMW\Tests\PHPUnitCompat;
use SMW\Tests\Utils\UtilityFactory;

/**
 * @group semantic-interlanguage-links
 * @group semantic-mediawiki-integration
 *
 * @group medium
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class I18nJsonFileIntegrityTest extends \PHPUnit\Framework\TestCase {

	use PHPUnitCompat;

	/**
	 * @covers I18nJsonFileIntegrity
	 * @dataProvider i18nFileProvider
	 */
	public function testI18NJsonDecodeEncode( $file ) {
		$jsonFileReader = UtilityFactory::getInstance()->newJsonFileReader( $file );

		$this->assertIsInt(

			$jsonFileReader->getModificationTime()
		);

		$this->assertIsArray(

			$jsonFileReader->read()
		);
	}

	public function i18nFileProvider() {
		$provider = [];
		$location = $GLOBALS['wgMessagesDirs']['SemanticInterlanguageLinks'];

		$bulkFileProvider = UtilityFactory::getInstance()->newBulkFileProvider( $location );
		$bulkFileProvider->searchByFileExtension( 'json' );

		foreach ( $bulkFileProvider->getFiles() as $file ) {
			$provider[] = [ $file ];
		}

		return $provider;
	}

}
