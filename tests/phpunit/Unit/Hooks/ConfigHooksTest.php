<?php

namespace SIL\Tests\Hooks;

use SIL\Hooks\ConfigHooks;

/**
 * @covers \SIL\Hooks\ConfigHooks
 * @group semantic-interlanguage-links
 *
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */
class ConfigHooksTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct(): void {
		$this->assertInstanceOf(
			ConfigHooks::class,
			new ConfigHooks()
		);
	}

	public function testOnSMWSettingsBeforeInitializationComplete(): void {
		$config = [
			'smwgFulltextSearchPropertyExemptionList' => []
		];

		$propertyExemptionList = [
			'__sil_iwl_lang',
			'__sil_ill_lang'
		];

		$instance = new ConfigHooks();
		$instance->onSMW__Settings__BeforeInitializationComplete( $config );

		$this->assertEquals(
			[
				'smwgFulltextSearchPropertyExemptionList' => $propertyExemptionList,
			],
			$config
		);
	}

}
