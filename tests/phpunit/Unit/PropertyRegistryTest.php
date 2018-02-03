<?php

namespace SIL\Tests;

use SIL\PropertyRegistry;
use SMW\PropertyRegistry as SemanticMediaWikiPropertyRegistry;
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
			PropertyRegistry::class,
			new PropertyRegistry()
		);
	}

	public function testILLRegister() {

		$semanticMediaWikiPropertyRegistry = SemanticMediaWikiPropertyRegistry::getInstance();

		$propertyRegistry = $this->getMockBuilder( '\SMW\PropertyRegistry' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new PropertyRegistry();
		$instance->register( $propertyRegistry );

		$this->assertNotEmpty(
			$semanticMediaWikiPropertyRegistry->findPropertyLabel( PropertyRegistry::SIL_ILL_LANG )
		);

		$this->assertNotEmpty(
			$semanticMediaWikiPropertyRegistry->findPropertyLabel( PropertyRegistry::SIL_ILL_REF )
		);

		$this->assertNotEmpty(
			$semanticMediaWikiPropertyRegistry->findPropertyLabel( PropertyRegistry::SIL_CONTAINER )
		);
	}

	public function testIWLRegister() {

		$semanticMediaWikiPropertyRegistry = SemanticMediaWikiPropertyRegistry::getInstance();

		$propertyRegistry = $this->getMockBuilder( '\SMW\PropertyRegistry' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new PropertyRegistry();
		$instance->register( $propertyRegistry );

		$this->assertNotEmpty(
			$semanticMediaWikiPropertyRegistry->findPropertyLabel( PropertyRegistry::SIL_IWL_LANG )
		);

		$this->assertNotEmpty(
			$semanticMediaWikiPropertyRegistry->findPropertyLabel( PropertyRegistry::SIL_IWL_REF )
		);

		$this->assertNotEmpty(
			$semanticMediaWikiPropertyRegistry->findPropertyLabel( PropertyRegistry::SIL_CONTAINER )
		);
	}

}
