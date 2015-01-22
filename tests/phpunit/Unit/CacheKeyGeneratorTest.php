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
				->setAuxiliaryVersionModifier( 'foo' )
				->getSiteCacheKey( 'foo' ),
			$instance
				->setAuxiliaryVersionModifier( 'bar' )
				->getSiteCacheKey( 'foo' )
		);

		$this->assertSame(
			$instance
				->setAuxiliaryVersionModifier( 'bar' )
				->getSiteCacheKey( 'foo' ),
			$instance
				->setAuxiliaryVersionModifier( 'bar' )
				->getSiteCacheKey( 'foo' )
		);
	}

	public function testGetPageLanguageCacheBlobKey() {

		$instance = new CacheKeyGenerator();

		$this->assertNotSame(
			$instance
				->setAuxiliaryVersionModifier( 'foo' )
				->getPageLanguageCacheBlobKey( 'foo' ),
			$instance
				->setAuxiliaryVersionModifier( 'bar' )
				->getPageLanguageCacheBlobKey( 'foo' )
		);
	}

	public function testGetPageCacheKey() {

		$instance = new CacheKeyGenerator();

		$this->assertNotSame(
			$instance
				->setAuxiliaryVersionModifier( 'foo' )
				->getPageCacheKey( 'foo', false ),
			$instance
				->setAuxiliaryVersionModifier( 'bar' )
				->getPageCacheKey( 'foo', false )
		);

		$this->assertSame(
			$instance
				->setAuxiliaryVersionModifier( 'foo' )
				->getPageCacheKey( 'foo', true ),
			$instance
				->setAuxiliaryVersionModifier( 'bar' )
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
