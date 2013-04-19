<?php
namespace Minibase\Plugin;

use Minibase\MB;

/**
 * Extend this abstract to create your own plugin.
 * Plugins should have methods for start, also preferable to have a stop method that unbinds 
 * events if you use these.
 * 
 * @author peec
 *
 */
abstract class Plugin {
	
	/**
	 * 
	 * @var Minibase\MB
	 */
	public $mb;

	/**
	 * @var array Array of configuration values IF any.
	 */
	public $config;
	
	public function __construct ($config) {
		$this->config = $config;
	}
	
	/**
	 * Plugin will run once $mb->initPlugin is called.
	 */
	abstract public function start();
	
	/**
	 * Override to provide setup for the plugin
	 */
	public function setup () {
		
	}
	
	
	/**
	 * Override to provide stop method for the plugin.
	 */
	public function stop () {
		
	}
	
	/**
	 * Sets the app instance. Minibase injects the $mb (Minibase\MB) object automatically.
	 * @param MB $app
	 */
	public function setApp (MB $mb) {
		$this->mb = $mb;
	}
	
	/**
	 * Gets configuration key.
	 * @param string $key The key
	 * @param mixed $defaultValue Default value. Default is NULL
	 */
	public function cfg($key, $defaultValue = null) {
		return is_array($this->config) && isset($this->config[$key]) ? $this->config[$key] : $defaultValue;
	}
}