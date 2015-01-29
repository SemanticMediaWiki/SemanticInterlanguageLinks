<?php

namespace SIL\Tests;

use SIL\PropertyRegistry;

use SMW\DIProperty;

/**
 * @covers \SIL\PropertyRegistry
 *
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class PropertyRegistryTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SIL\PropertyRegistry',
			new PropertyRegistry()
		);
	}

	public function testILLRegister() {

		$instance = new PropertyRegistry();
		$instance->register();

		$this->assertNotEmpty(
			DIProperty::findPropertyLabel( PropertyRegistry::SIL_ILL_LANG )
		);

		$this->assertNotEmpty(
			DIProperty::findPropertyLabel( PropertyRegistry::SIL_ILL_REF )
		);

		$this->assertNotEmpty(
			DIProperty::findPropertyLabel( PropertyRegistry::SIL_CONTAINER )
		);
	}

	public function testIWLRegister() {

		$instance = new PropertyRegistry();
		$instance->register();

		$this->assertNotEmpty(
			DIProperty::findPropertyLabel( PropertyRegistry::SIL_IWL_LANG )
		);

		$this->assertNotEmpty(
			DIProperty::findPropertyLabel( PropertyRegistry::SIL_IWL_REF )
		);

		$this->assertNotEmpty(
			DIProperty::findPropertyLabel( PropertyRegistry::SIL_CONTAINER )
		);
	}

}
