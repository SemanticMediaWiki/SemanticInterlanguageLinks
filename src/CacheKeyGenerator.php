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
	 * External (hence auxiliary) cache key modifier that can be used to alter
	 * existing keys
	 */
	private $auxiliaryKeyModifier = '';

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
	 * @param string $auxiliaryKeyModifier
	 *
	 * @return CacheKeyGenerator
	 */
	public function setAuxiliaryKeyModifier( $auxiliaryKeyModifier ) {
		$this->auxiliaryKeyModifier = $auxiliaryKeyModifier;
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
		return $this->getCachePrefix() . 's:' . md5( $key . $this->auxiliaryKeyModifier );
	}

	/**
	 * @since 1.0
	 *
	 * @param string $key
	 *
	 * @return  string
	 */
	public function getPageLanguageCacheBlobKey( $key = '' ) {
		return $this->getCachePrefix() . 'b:' . md5( $key . $this->auxiliaryKeyModifier );
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
		return $this->getCachePrefix() . 'p:' . md5( $key . ( $stable ? '' : $this->auxiliaryKeyModifier ) );
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
