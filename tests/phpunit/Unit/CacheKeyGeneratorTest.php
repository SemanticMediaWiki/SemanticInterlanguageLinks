<?php

namespace SIL\Tests;

use SIL\CacheKeyGenerator;

/**
 * @covers \SIL\CacheKeyGenerator
 *
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CacheKeyGeneratorTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SIL\CacheKeyGenerator',
			new CacheKeyGenerator()
		);
	}

	public function testGetSiteCacheKey() {

		$instance = new CacheKeyGenerator();

		$this->assertNotSame(
			$instance
				->setAuxiliaryKeyModifier( 'foo' )
				->getSiteCacheKey( 'foo' ),
			$instance
				->setAuxiliaryKeyModifier( 'bar' )
				->getSiteCacheKey( 'foo' )
		);

		$this->assertSame(
			$instance
				->setAuxiliaryKeyModifier( 'bar' )
				->getSiteCacheKey( 'foo' ),
			$instance
				->setAuxiliaryKeyModifier( 'bar' )
				->getSiteCacheKey( 'foo' )
		);
	}

	public function testGetPageLanguageCacheBlobKey() {

		$instance = new CacheKeyGenerator();

		$this->assertNotSame(
			$instance
				->setAuxiliaryKeyModifier( 'foo' )
				->getPageLanguageCacheBlobKey( 'foo' ),
			$instance
				->setAuxiliaryKeyModifier( 'bar' )
				->getPageLanguageCacheBlobKey( 'foo' )
		);
	}

	public function testGetPageCacheKey() {

		$instance = new CacheKeyGenerator();

		$this->assertNotSame(
			$instance
				->setAuxiliaryKeyModifier( 'foo' )
				->getPageCacheKey( 'foo', false ),
			$instance
				->setAuxiliaryKeyModifier( 'bar' )
				->getPageCacheKey( 'foo', false )
		);

		$this->assertSame(
			$instance
				->setAuxiliaryKeyModifier( 'foo' )
				->getPageCacheKey( 'foo', true ),
			$instance
				->setAuxiliaryKeyModifier( 'bar' )
				->getPageCacheKey( 'foo', true )
		);
	}

	public function testPrefixModification() {

		$instance = new CacheKeyGenerator();

		$this->assertNotSame(
			$instance
				->setCachePrefix( 'foo' )
				->getSiteCacheKey( 'foo' ),
			$instance
				->setCachePrefix( 'bar' )
				->getSiteCacheKey( 'foo' )
		);
	}

}
