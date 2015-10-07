<?php

namespace SIL\Tests;

use SIL\CacheKeyProvider;

/**
 * @covers \SIL\CacheKeyProvider
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CacheKeyProviderTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SIL\CacheKeyProvider',
			new CacheKeyProvider()
		);
	}

	public function testGetSiteCacheKey() {

		$instance = new CacheKeyProvider();

		$this->assertSame(
			$instance->getSiteCacheKey( 'foo' ),
			$instance->getSiteCacheKey( 'foo' )
		);
	}

	public function testGetPageLanguageCacheBlobKey() {

		$instance = new CacheKeyProvider();

		$this->assertSame(
			$instance->getPageLanguageCacheBlobKey( 'foo' ),
			$instance->getPageLanguageCacheBlobKey( 'foo' )
		);
	}

	public function testGetPageCacheKey() {

		$instance = new CacheKeyProvider();

		$this->assertSame(
			$instance->getPageCacheKey( 'foo', true ),
			$instance->getPageCacheKey( 'foo', true )
		);
	}

	public function testPrefixModification() {

		$instance = new CacheKeyProvider();

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
