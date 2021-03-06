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
		$this->appDir = $appDir;
		$this->mb = $mb;
		
		$json = $this->replaceStringVars($json, array(
				'APP_DIR' => $this->appDir
		));
		
		$jsonData = json_decode($json);
		if ($jsonData === null) {
			throw new \Exception("Configuration to MBConfigurationParsaer must be valid JSON.");
		}
		$this->data = $jsonData;
		
	}
	
	/**
	 * Parses the JSON file and append objects to Minibase based on this configuration.
	 */
	public function parse () {
		$mb = $this->mb;
		$appDir = $this->appDir;
		
		$rootNode = $this->data;
		
		
		$this->assign("autoLoaders", $this->data, function ($value) use ($mb, $rootNode) {
			$vendorDir = $this->assign("vendorDir", $rootNode, null, true, self::T_STRING);
			
			foreach($value as $aLoader){
				$ns = $this->replaceNS($this->assign("ns", $aLoader, null, true, self::T_STRING));
				$path = $this->assign("path", $aLoader, null, true, self::T_STRING);
		
				$loader = require("{$vendorDir}/autoload.php");
				$loader->add($ns, $path);
		
			}
		}, false, self::T_ARRAY);
		
		$this->assign("i18n", $this->data, function ($value) use ($mb) {
			
			$this->assign("availableLanguages", $value, function ($availableLanguages) use($mb) {
				$mb->trans->setAvailableLanguages($availableLanguages);
			}, false, self::T_ARRAY);
			
			$this->assign("defaultLocale", $value, function ($defaultLocale) use($mb) {
				$mb->trans->setLocale($defaultLocale);
			}, false, self::T_STRING);
			
			$this->assign("defaultDomain", $value, function ($defaultDomain) use($mb) {
				$mb->trans->switchDomain($defaultDomain);
			}, false, self::T_STRING);
			
			$this->assign("localeRepositories", $value, function ($localeRepositories) use($mb) {
				
				foreach($localeRepositories as $localeRepository) {
					$localeDir = $this->assign("localeDir", $localeRepository, null,         true, self::T_STRING, null);
					$locale = $this->assign("locale", $localeRepository, null,         true, self::T_STRING, null);
					$rootDirs = $this->assign("rootDirs", $localeRepository, null,           true, self::T_ARRAY, null);
					$domain = $this->assign("domain", $localeRepository, null,    true, self::T_STRING);
					$charset = 	$this->assign("charset", $localeRepository, null, false, self::T_STRING, 'UTF-8');
					
					$mb->trans->load($domain, $localeDir, $locale, $rootDirs, $charset);
				}
				
			}, false, self::T_ARRAY);
			
		}, false, self::T_OBJECT);
		
		
		$this->assign("routeFiles", $this->data, function ($value) use ($mb) {
			foreach($value as $file){
				$mb->loadRouteFile($file);
			}
		}, false, self::T_ARRAY);
		
		$this->assign("eventCollections", $this->data, function ($value) use ($mb) {
			foreach($value as $event){
				$event = $this->replaceNS($event);
				$mb->addEventCollection(new $event());
			}
		}, false, self::T_ARRAY);
		
		
		
		$this->assign("config", $this->data, function ($value) use ($mb) {
			foreach($value as $k => $v){
				$mb->setConfig($k, $v);
			}
		}, false, self::T_OBJECT);
		
		
		$this->assign("sessionDriver", $this->data, function ($value) use ($mb) {
			$name = $this->replaceNS($this->assign("name", $value, null, true, self::T_STRING));
			$config = $this->assign("config", $value, null, false, self::T_OBJECT, array());
			$options = $this->assign("options", $value, null, false, self::T_OBJECT, array());
			$mb->configureSessionDriver(new $name(), $this->objectToArray($config), $this->objectToArray($options));
		}, false, self::T_OBJECT);
		
		
		$this->assign("cacheDriver", $this->data, function ($value) use ($mb) {
			$name = $this->replaceNS($this->assign("name", $value, null, true, self::T_STRING));
			$config = $this->assign("config", $value, null, false, self::T_OBJECT, array());
			$mb->configureCacheDriver(new $name(), $this->objectToArray($config));
				
				
		}, false, self::T_OBJECT);
		
		$this->assign("plugins", $this->data, function ($value) use ($mb) {
			foreach($value as $plugin) {
				$name = $this->replaceNS($this->assign("name", $plugin, null, true, self::T_STRING));
				$config = $this->assign("config", $plugin, null, false, self::T_OBJECT, null);
				
				// Try to init the plugin.
				$mb->initPlugins(array($name => $this->objectToArray($config)));
			}
		}, false, self::T_ARRAY);
		
		
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
	
	
	public function replaceStringVars ($str, $varMap = array()) {
		
		$patterns = array(
				'#(\$)\{([a-zA-Z_]*)\}#', // simple ${Myvar_}
				'#(\$_ENV)\{([a-zA-Z_]*)\}#', // simple $_ENV{Myvar_}
				
		);
		
		
		$str = preg_replace_callback($patterns, function ($keys) use($varMap) {
			$key = $keys[1];
			$val = $keys[2];
			if ($key === '$'){
				if (!in_array($val, array_keys($varMap))) {
					throw new \Exception ("Could not find variable $key\{$val\} in JSON configuration file.");
				}
			} else if ($key === '$_ENV') {
				$value = getenv($val);
				if ($value === false) {
					throw new \Exception ("Could not find environment variable $val using $key{{$val}}. Not set for this environment.");
				}
				return $value;
			}
			
			$value = $varMap[$val];
			$value = str_replace('\\', '\\\\', $value); // Windows.
			return $value;
		}, $str);
		
		
		
		return $str;
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