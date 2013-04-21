<?php
namespace Minibase;

/**
 * Configuration loader for Minibase.
 * 
 * Can load:
 * 
 * - plugins
 * - event collections
 * - route files
 * - set MB configuration
 * - set Cache driver
 * 
 * @author peec
 *
 */
class MBConfigurationParser {
	const T_ARRAY = "array";
	const T_STRING = "string";
	const T_BOOLEAN = "boolean";
	const T_NUMBER = "double,float,integer";
	const T_OBJECT = "object";
	const T_NULL = "NULL";
	
	
	private $data;
	private $mb;
	private $appDir;
	
	/**
	 * 
	 * @param string $file Path to the JSON file that your want to load.
	 * @param Minibase\MB $mb
	 * @param string $appDir Application directory (base path).
	 */
	static public function fromFile ($file, MB $mb, $appDir = null) {
		return new MBConfigurationParser(file_get_contents($file), $mb, $appDir);
	}
	
	
	public function __construct ($json, MB $mb, $appDir = null) {
		$jsonData = json_decode($json);
		if ($jsonData === null) {
			throw new \Exception("Configuration to MBConfigurationParsaer must be valid JSON.");
		}
		$this->data = $jsonData;
		$this->mb = $mb;
		$this->appDir = $appDir;
	}
	
	/**
	 * Parses the JSON file and append objects to Minibase based on this configuration.
	 */
	public function parse () {
		$mb = $this->mb;
		$appDir = $this->appDir;
		
		$this->assign("routeFiles", $this->data, function ($value) use ($mb) {
			foreach($value as $file){
				$mb->loadRouteFile($this->fileDir($file));
			}
		}, false, self::T_ARRAY);
		
		$this->assign("eventCollections", $this->data, function ($value) use ($mb) {
			foreach($value as $event){
				$event = $this->replaceNS($event);
				$mb->addEventCollection(new $event());
			}
		}, false, self::T_ARRAY);
		
		$this->assign("autoLoaders", $this->data, function ($value) use ($mb, $appDir) {
			foreach($value as $aLoader){
				if (!$appDir){
					throw new \Exception("App directory must be defined in order to use autoLoaders configuration.");
				}
				$ns = $this->replaceNS($this->assign("ns", $aLoader, null, true, self::T_STRING));
				$path = $this->assign("path", $aLoader, null, true, self::T_STRING);
				
				$loader = require $this->fileDir("vendor/autoload.php");
				$loader->add($ns, $this->fileDir($path));
				
			}
		}, false, self::T_ARRAY);
		
		$this->assign("config", $this->data, function ($value) use ($mb) {
			foreach($value as $k => $v){
				$mb->setConfig($k, $v);
			}
		}, false, self::T_OBJECT);
		
		$this->assign("plugins", $this->data, function ($value) use ($mb) {
			foreach($value as $plugin) {
				$name = $this->replaceNS($this->assign("name", $plugin, null, true, self::T_STRING));
				$config = $this->assign("config", $plugin, null, false, self::T_OBJECT, null);
				
				// Try to init the plugin.
				$mb->initPlugins(array($name => $this->objectToArray($config)));
			}
		}, false, self::T_ARRAY);
		
		
		$this->assign("cacheDriver", $this->data, function ($value) use ($mb) {
			$name = $this->replaceNS($this->assign("name", $value, null, true, self::T_STRING));
			$config = $this->assign("config", $value, null, false, self::T_OBJECT, array());
			$mb->setCacheDriver(new $name(), $this->objectToArray($config));
			
		}, false, self::T_OBJECT);
		
	}
	
	/**
	 * Internal function to allow error reporting and syntax requirements for the json file.
	 * @param string $key The key to look for
	 * @param stdClass $node The node we're in.
	 * @param callable $func A callback function (optional) Takes the value of $node->$key
	 * @param boolean $required If required set to true.
	 * @param string $validate Can be one of the T_* constants.
	 * @param mixed $defaultValue Default value to return from the assign function.
	 * @throws \Exception
	 */
	protected function assign ($key,$node, $func, $required = false, $validate = false, $defaultValue = null) {
		if (isset($node->$key)) {
			$val = $node->$key;
			if ($validate !== false){
				$type = gettype($val);
				if ($type !== $validate) {
					throw new \Exception ("MB Configuration expected type($validate) was ($type) for key($key).");
				}
			}
			if ($func !== null){
				$func($val);
			}
			return $val;
		} else {
			if ($required === true) {
				throw new \Exception ("$key is required in Node.");
			}
		}
		return $defaultValue;
	}
	
	
	/**
	 * Returns a file path to a file based on appDir if its defined.
	 * @param string $file The file path.
	 */
	public function fileDir ($file) {
		$path = "";
		if ($this->appDir) {
			$path .= $this->appDir;
			if (substr($path,-1) !== DIRECTORY_SEPARATOR) {
				$path .= DIRECTORY_SEPARATOR;
			}
		}
		return $path . $file;
	}
	
	/**
	 * Configuration uses / for namespace separator. NOT \.
	 * @param unknown_type $str
	 */
	protected function replaceNS ($str) {
		return str_replace('/', '\\', $str);
	}
	
	/**
	 * Recursivly convert Object to Multidimensional Array.
	 * Useful for json objects.
	 * @param mixed $d The value
	 */
	protected function objectToArray($d) {
		if (is_object($d)) {
			// Gets the properties of the given object
			// with get_object_vars function
			$d = get_object_vars($d);
		}	
		if (is_array($d)) {
			
			return array_map(array($this, 'objectToArray'), $d);
		}
		else {
			// Return array
			return $d;
		}
	}
}