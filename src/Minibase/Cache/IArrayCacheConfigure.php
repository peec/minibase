<?php
namespace Minibase\Cache;

/**
 * 
 * @author peec
 *
 */
interface IArrayCacheConfigure {
	/**
	 * 
	 * @param array $config Custom array of configuration for the speicifc cache driver.
	 */
	public function setup (array $config);
	
	/**
	 * @return Doctrine\Common\Cache\CacheProvider Doctrine Cache provider.
	 */
	public function getDriver();
	
}