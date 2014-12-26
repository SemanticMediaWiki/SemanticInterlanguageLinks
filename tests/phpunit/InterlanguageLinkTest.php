<?php

namespace SIL\Tests;

use SIL\InterlanguageLink;
use SIL\PropertyRegistry;

/**
 * @covers \SIL\InterlanguageLink
 *
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class InterlanguageLinkTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SIL\InterlanguageLink',
			new InterlanguageLink( 'en', 'Foo' )
		);
	}

	public function testConstructorArgumentGetter() {

		$instance = new InterlanguageLink( 'en', 'Foo' );

		$this->assertEquals(
			'en',
			$instance->getLanguageCode()
		);

		$this->assertInstanceOf(
			'\Title',
			$instance->getLinkReference()
		);
	}

	public function testConstructDataValue() {

		$instance = new InterlanguageLink( 'en', 'Foo' );

		$this->assertInstanceOf(
			'\SMWDataValue',
			$instance->newLanguageDataValue()
		);

		$this->assertEquals(
			PropertyRegistry::SIL_LANG,
			$instance->newLanguageDataValue()->getProperty()->getKey()
		);

		$this->assertInstanceOf(
			'\SMWDataValue',
			$instance->newLinkReferenceDataValue()
		);

		$this->assertEquals(
			PropertyRegistry::SIL_REF,
			$instance->newLinkReferenceDataValue()->getProperty()->getKey()
		);

		$this->assertInstanceOf(
			'\SMW\DIProperty',
			$instance->newInterlanguageLinkContainerProperty()
		);

		$this->assertEquals(
			PropertyRegistry::SIL_CONTAINER,
			$instance->newInterlanguageLinkContainerProperty()->getKey()
		);
	}

	public function testGetHash() {

		$instance = new InterlanguageLink( 'en', 'Foo' );

		$this->assertContains(
			'en#Foo',
			$instance->getHash()
		);
	}

	public function testGetContainerId() {

		$instance = new InterlanguageLink( 'en', 'Foo' );

		$this->assertContains(
			'sil.en',
			$instance->getContainerId()
		);
	}

}
