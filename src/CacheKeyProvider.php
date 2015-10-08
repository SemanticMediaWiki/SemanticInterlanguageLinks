<?php

namespace SIL;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CacheKeyProvider {

	/**
	 * Update the version to force a recache for all items due to
	 * required changes
	 */
	const VERSION = '1.1';

	/**
	 * @var string|null
	 */
	private $cachePrefix = null;

	/**
	 * @since 1.0
	 *
	 * @param string $cachePrefix
	 */
	public function __construct( $cachePrefix ) {
		$this->cachePrefix = $cachePrefix;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $key
	 *
	 * @return  string
	 */
	public function getSiteCacheKey( $key ) {
		return $this->cachePrefix . ':sil:site:' . md5( $key . self::VERSION );
	}

	/**
	 * @since 1.0
	 *
	 * @param string $key
	 *
	 * @return  string
	 */
	public function getPageLanguageCacheBlobKey( $key = '' ) {
		return $this->cachePrefix . ':sil:blob:' . md5( $key . self::VERSION );
	}

	/**
	 * @since 1.0
	 *
	 * @param string $key
	 * @param boolean $stable
	 *
	 * @return  string
	 */
	public function getPageCacheKey( $key, $stable = true ) {
		return $this->cachePrefix . ':sil:page:' . md5( $key . ( $stable ? '' : self::VERSION ) );
	}

}
