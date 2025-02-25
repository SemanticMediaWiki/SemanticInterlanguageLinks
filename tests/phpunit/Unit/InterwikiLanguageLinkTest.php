<?php

namespace SIL\Tests;

use SIL\InterwikiLanguageLink;
use SIL\PropertyRegistry;
use SMW\Tests\PHPUnitCompat;
use Title;

/**
 * @covers \SIL\InterwikiLanguageLink
 *
 * @group semantic-interlanguage-links
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class InterwikiLanguageLinkTest extends \PHPUnit\Framework\TestCase {

	use PHPUnitCompat;

	protected function setUp(): void {
		parent::setUp();

		$propertyRegistry = $this->getMockBuilder( '\SMW\PropertyRegistry' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new PropertyRegistry();
		$instance->register( $propertyRegistry );
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			'\SIL\InterwikiLanguageLink',
			new InterwikiLanguageLink( 'en:Foo' )
		);
	}

	public function testConstructorArgumentGetter() {
		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'getInterwiki' )
			->willReturn( 'en' );

		$instance = new InterwikiLanguageLink( $title );

		$this->assertEquals(
			'en',
			$instance->getLanguageCode()
		);

		$this->assertInstanceOf(
			'\Title',
			$instance->getInterwikiReference()
		);
	}

	public function testConstructDataValue() {
		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->any() )
			->method( 'getInterwiki' )
			->willReturn( 'en' );

		$title->expects( $this->any() )
			->method( 'getNamespace' )
			->willReturn( NS_MAIN );

		$title->expects( $this->any() )
			->method( 'getDBKey' )
			->willReturn( 'Foo' );

		$instance = new InterwikiLanguageLink( $title );

		$this->assertInstanceOf(
			'\SMWDataValue',
			$instance->newLanguageDataValue()
		);

		$this->assertEquals(
			PropertyRegistry::SIL_IWL_LANG,
			$instance->newLanguageDataValue()->getProperty()->getKey()
		);

		$this->assertInstanceOf(
			'\SMWDataValue',
			$instance->newInterwikiReferenceDataValue()
		);

		$this->assertEquals(
			PropertyRegistry::SIL_IWL_REF,
			$instance->newInterwikiReferenceDataValue()->getProperty()->getKey()
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
		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->any() )
			->method( 'getInterwiki' )
			->willReturn( 'en' );

		$title->expects( $this->any() )
			->method( 'getPrefixedText' )
			->willReturn( 'Foo' );

		$instance = new InterwikiLanguageLink( $title );

		$this->assertContains(
			'en#Foo',
			$instance->getHash()
		);
	}

	public function testGetContainerId() {
		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'getInterwiki' )
			->willReturn( 'en' );

		$instance = new InterwikiLanguageLink( $title );

		$this->assertContains(
			'iwl.en',
			$instance->getContainerId()
		);
	}

	public function testGetInterwikiReference() {
		$linkReference = Title::newFromText( 'en:Foo' );

		$instance = new InterwikiLanguageLink( 'en:Foo' );

		$this->assertSame(
			$linkReference,
			$instance->getInterwikiReference()
		);
	}

}
