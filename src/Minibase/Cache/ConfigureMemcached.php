<?php
namespace Minibase\Cache;

use Doctrine\Common\Cache\MemcachedCache;

class ConfigureMemcached implements IArrayCacheConfigure{

	/**
	 * @var Doctrine\Common\Cache\CacheProvider
	 */
	public $driver;
	
	public function setup (array $conf) {
		$memcached = new \Memcached();
		
		if (!isset($conf['servers'])) {
			throw new \Exception ("'servers' array for memcached must be defined. Atleast one server must be added.");
		}
		
		$memcached->addServers($conf['servers']);
		if (isset($conf['callback'])) {
			$call = $conf['callback']->bindTo($this->driver);
			$call();
		}

		$this->driver = new MemcachedCache();
		$this->driver->setMemcached($memcached);
	}
	
	public function getDriver () {
		return $this->driver;
	}
		
}