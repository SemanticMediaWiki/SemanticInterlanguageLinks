<?php

namespace SIL\Tests;

use SIL\InterlanguageLink;
use SIL\PropertyRegistry;
use SMW\Tests\PHPUnitCompat;

use Title;

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

	use PHPUnitCompat;

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
			PropertyRegistry::SIL_ILL_LANG,
			$instance->newLanguageDataValue()->getProperty()->getKey()
		);

		$this->assertInstanceOf(
			'\SMWDataValue',
			$instance->newLinkReferenceDataValue()
		);

		$this->assertEquals(
			PropertyRegistry::SIL_ILL_REF,
			$instance->newLinkReferenceDataValue()->getProperty()->getKey()
		);

		$this->assertInstanceOf(
			'\SMW\DIProperty',
			$instance->newContainerProperty()
		);

		$this->assertEquals(
			PropertyRegistry::SIL_CONTAINER,
			$instance->newContainerProperty()->getKey()
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
			'ill.en',
			$instance->getContainerId()
		);
	}

	public function testGetLinkReference() {

		$linkReference = Title::newFromText( __METHOD__ );

		$instance = new InterlanguageLink( 'en', $linkReference );

		$this->assertSame(
			$linkReference,
			$instance->getLinkReference()
		);

		$instance = new InterlanguageLink( 'en', __METHOD__ );

		$this->assertEquals(
			$linkReference,
			$instance->getLinkReference()
		);
	}

}
