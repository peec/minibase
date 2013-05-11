<?php
namespace Minibase\Session;


use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler;

/**
 * 
 * @author peec
 *
 */
class ConfigureMemcachedSession implements IArraySessionConfigure {

	private $handler;
	
	/**
	 * (non-PHPdoc)
	 * @see Minibase\Session.IArraySessionConfigure::setup()
	 */
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
		$options = isset($conf['options']) ? $conf['options'] : array();
		
		
		$this->handler = new MemcachedSessionHandler($memcached, $options);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Minibase\Session.IArraySessionConfigure::getHandler()
	 */
	public function getHandler () {
		return $this->handler;
	}
	
}