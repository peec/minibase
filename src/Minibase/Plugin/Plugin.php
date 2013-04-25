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
		if (method_exists($this, 'defaultConfig')) {
			$config = $this->array_merge_recursive_distinct($this->defaultConfig(), $config);
		}
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
	
	
	
	/**
	 * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
	 * keys to arrays rather than overwriting the value in the first array with the duplicate
	 * value in the second array, as array_merge does. I.e., with array_merge_recursive,
	 * this happens (documented behavior):
	 *
	 * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
	 *     => array('key' => array('org value', 'new value'));
	 *
	 * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
	 * Matching keys' values in the second array overwrite those in the first array, as is the
	 * case with array_merge, i.e.:
	 *
	 * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
	 *     => array('key' => 'new value');
	 *
	 * Parameters are passed by reference, though only for performance reasons. They're not
	 * altered by this function.
	 *
	 * @param array $array1
	 * @param mixed $array2
	 * @author daniel@danielsmedegaardbuus.dk
	 * @return array
	 */
	public function &array_merge_recursive_distinct(array &$array1, &$array2 = null) {
		$merged = $array1;
	
		if (is_array($array2))
			foreach ($array2 as $key => $val)
			if (is_array($array2[$key]))
			$merged[$key] = isset($merged[$key]) && is_array($merged[$key]) ? $this->array_merge_recursive_distinct($merged[$key], $array2[$key]) : $array2[$key];
		else
			$merged[$key] = $val;
	
		return $merged;
	}
}