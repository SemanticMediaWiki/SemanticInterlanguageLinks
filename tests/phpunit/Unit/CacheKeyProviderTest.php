<?php

namespace SIL\Tests;

use SIL\CacheKeyProvider;

/**
 * @covers \SIL\CacheKeyProvider
 * @group semantic-interlanguage-links
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class CacheKeyProviderTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$this->assertInstanceOf(
			'\SIL\CacheKeyProvider',
			new CacheKeyProvider( 'foo' )
		);
	}

	public function testGetSiteCacheKey() {
		$instance = new CacheKeyProvider( 'foo' );

		$this->assertSame(
			$instance->getSiteCacheKey( 'foo' ),
			$instance->getSiteCacheKey( 'foo' )
		);
	}

	public function testGetPageLanguageCacheBlobKey() {
		$instance = new CacheKeyProvider( 'foo' );

		$this->assertSame(
			$instance->getPageLanguageCacheBlobKey( 'foo' ),
			$instance->getPageLanguageCacheBlobKey( 'foo' )
		);
	}

	public function testGetPageCacheKey() {
		$instance = new CacheKeyProvider( 'foo' );

		$this->assertSame(
			$instance->getPageCacheKey( 'foo', true ),
			$instance->getPageCacheKey( 'foo', true )
		);
	}

}
