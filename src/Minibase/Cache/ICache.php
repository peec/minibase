<?php
namespace Minibase\Cache;

/**
 * MB uses this interface for caching.
 * If you want custom cache engines, implement this simple interface.
 * @author peec
 *
 */
interface ICache {
	const KEY_NOT_FOUND = null;
	
	/**
	 * 
	 * @param array $config Custom array of configuration for the speicifc cache driver.
	 */
	public function setup (array $config);
	
	/**
	 * Gets something from the cache
	 * Should return exactly ICache::KEY_NOT_FOUND if not found in cache.
	 * @param string $key the key .
	 */
	public function get($key);
	
	/**
	 * Should delete a key from the cache store
	 * @param string $key The key
	 */
	public function delete ($key);
	
	/**
	 * Assign some $value to a $key.
	 * @param string_type $key A key.
	 * @param mixed $value A value
	 * @param int $expire Expire in seconds , 0 if lasting "forever".
	 */
	public function set($key, $value, $expireInSeconds = 0);
	
}