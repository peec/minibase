<?php
namespace Minibase\Cache\Memcached;

use Minibase\Cache\ICache;

class MemcachedDriver implements ICache{

	public $driver;
	
	public function setup (array $conf) {
		$this->driver = new \Memcached();
		
		if (!isset($conf['servers'])) {
			throw new \Exception ("'servers' array for memcached must be defined. Atleast one server must be added.");
		}
		
		$this->driver->addServers($conf['servers']);
		if (isset($conf['callback'])) {
			$call = $conf['callback']->bindTo($this->driver);
			$call();
		}
		
	}
	
	public function get ($key) {
		$res = $this->driver->get($key);
		
		return $res === false ? ICache::KEY_NOT_FOUND : $res;
	}
	
	public function delete ($key) {
		$this->driver->delete($key);
	}
	
	public function set($key, $value, $expireInSeconds = 0) {
		$this->driver->set($key, $value, $expireInSeconds);	
	}
	
}