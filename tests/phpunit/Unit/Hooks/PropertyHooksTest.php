<?php

namespace SIL\Tests\Hooks;

use SIL\Hooks\PropertyHooks;
use SMW\PropertyRegistry;

/**
 * @covers \SIL\Hooks\PropertyHooks
 * @group semantic-interlanguage-links
 *
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */
class PropertyHooksTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct(): void {
		$this->assertInstanceOf(
			PropertyHooks::class,
			new PropertyHooks()
		);
	}

	public function testOnSMWPropertyInitProperties(): void {
		$propertyRegistry = $this->getMockBuilder( PropertyRegistry::class )
			->disableOriginalConstructor()
			->getMock();

		$instance = new PropertyHooks();

		$this->assertTrue(
			$instance->onSMW__Property__initProperties( $propertyRegistry )
		);
	}

}
