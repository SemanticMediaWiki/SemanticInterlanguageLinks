<?php

namespace SIL;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CacheKeyGenerator {

	/**
	 * Stable cache auxiliary identifier, to be changed in cases where the
	 * cache key needs an auto-update
	 */
	private $auxiliaryVersionModifier = '';

	/**
	 * @var string|null
	 */
	private $cachePrefix = null;

	/**
	 * @since 1.0
	 *
	 * @param string $cachePrefix
	 *
	 * @return CacheKeyGenerator
	 */
	public function setCachePrefix( $cachePrefix ) {
		$this->cachePrefix = $cachePrefix;
		return $this;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $auxiliaryVersionModifier
	 *
	 * @return CacheKeyGenerator
	 */
	public function setAuxiliaryVersionModifier( $auxiliaryVersionModifier ) {
		$this->auxiliaryVersionModifier = $auxiliaryVersionModifier;
		return $this;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $key
	 *
	 * @return  string
	 */
	public function getSiteCacheKey( $key ) {
		return $this->getCachePrefix() . 's:' . md5( $key . $this->auxiliaryVersionModifier );
	}

	/**
	 * @since 1.0
	 *
	 * @param string $key
	 *
	 * @return  string
	 */
	public function getPageLanguageCacheBlobKey( $key = '' ) {
		return $this->getCachePrefix() . 'b:' . md5( $key . $this->auxiliaryVersionModifier );
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
		return $this->getCachePrefix() . 'p:' . md5( $key . ( $stable ? '' : $this->auxiliaryVersionModifier ) );
	}

	/**
	 * @since 1.0
	 *
	 * @return  string
	 */
	private function getCachePrefix() {
		return $this->cachePrefix . ':' . 'sil:';
	}

}
