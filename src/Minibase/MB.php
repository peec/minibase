<?php
namespace Minibase;

use Doctrine\Common\Cache\CacheProvider;

use Minibase\Cache\IArrayCacheConfigure;
use Doctrine\Common\Cache\ArrayCache;
use Minibase\Wreqr\EventCollection;
use Minibase\Mvc\Call;
use Minibase\Mvc\RouteParser\RouteParser;
use Minibase\Plugin\Plugin;
use Minibase\Http\Response;
use Minibase\Http\Request;
use Minibase\Wreqr\EventBinder;
use Minibase\Http;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

/**
 * Application stub for a simple application.
 * @property mixed $plugins
 * @author peec
 */
class MB{
	
	/**
	 * Array of plugins registered with the `plugin` method.
	 * @var array Array of plugins
	 */
	private $plugins = array();
	
	/**
	 * The global event binder.
	 * @var Minibase\Wreqr\EventBinder
	 */
	public $events;

	/**
	 * The HTTP request object.
	 * @var Minibase\Http\Request
	 */
	public $request;
	
	
	protected $routes = array();
	

	/**
	 * Configuration set by setConfig
	 * @var array Array of configuration.
	 */
	public $cfg = array();
	
	/**
	 * The current call being executed. Call has useful method such as reverse routing.
	 * @var Minibase\Mvc\Call
	 */
	public $call;
	
	/**
	 * Array of reverse calls available for reversion.
	 * @var array Array of reverse calls.
	 * @see MB::route
	 */
	public $reverseCalls;
	
	
	public $annotationReader;
	
	/**
	 * 
	 * @var string Application environment.
	 */
	public $applicationEnv;
	
	/**
	 * If created with MB::cli this var will be instance of Minibase\MBConsole.
	 * @var Minibase\MBConsole 
	 */
	public $console;

	/**
	 * The current cache provider.
	 * @var Doctrine\Common\Cache\CacheProvider
	 */
	public $cache;
	
	const VERSION = "1.0.0a";
	
	/**
	 * 
	 * @var string Config key for view path.
	 */
	const CFG_VIEWPATH = "viewPath";
	
	
	
	/**
	 * Factory a new application
	 * Uses global variables to create nested objects.
	 * @return \Minibase\MB Returns a new object of Minibase.
	 */
	static public function create () {
		$mb = new MB();
		$mb->events = new EventBinder();
		$mb->request = Http\Request::createFromGlobals();
		$mb->request->setMB($mb);
		return $mb;
	}
	
	static public function cli () {
		$mb = new MB();
		$mb->events = new EventBinder();
		$mb->request = new Http\Request();
		
		$mb->console = new MBConsole($mb);
		return $mb;
	}
	
	
	public function __construct() {
		AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Annotations.php');
		$this->annotationReader = new AnnotationReader();
		
		// Find out if in development or not
		$this->applicationEnv = (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development');
		
		if ($this->isDevelopment()) {
			$this->cache = new ArrayCache();
		}
	}
	
	/**
	 * Routes HTTP requests against closure callbacks.
	 * 
	 * @param string $method The Request method (get,post,put,delete,etc..)
	 * @param string $url The url starting with backslash, ie. "/" or "/hello/(\d+)". Can have regexp.
	 * @param string $call A callback (closure) to run if the url and http method matches.
	 * @param string $reverseKey A reverse route key that is unique to this route. 
	 * @throws InvalidControllerReturnException
	 */
	public function route ($method, $url, $call, $reverseKey = null) {
		$url .= substr($url, -1) != '/' ? '/' : '';
		
		$call = new Call($call, array($method, $url, $reverseKey));
		$call->setMB($this);
		if ($reverseKey !== null) {
			$this->reverseCalls[$reverseKey] = &$call;
		}
		array_push($this->routes, array($method, $url, $call));
	}
	
	/**
	 * Gets a call based on reverse key
	 * 
	 * @param string $reverseKey The key to reverse. If routing file is used use Controller.method syntax. Else use supplied $reverseKey.
	 * @return Minibase\Mvc\Call A Call object, you may use reverse() on the call to get the URL for the call.
	 */
	public function & call ($reverseKey) {
		if (!isset($this->reverseCalls[$reverseKey])) {
			throw new \Exception ("Can not reverse call with $reverseKey, no such reverse Call found.");
		}
		return $this->reverseCalls[$reverseKey];
	}
	
	
	/**
	 * Executes a route if it's correct uri and request method.
	 * @param string $method HTTP request method, ie. get,post,delete,put,patch
	 * @param string $url url starting with "/"
	 * @param callback $call Callback.
	 */
	public function executeRoute ($method, $url, Call $call) {
			
		if ($this->request->method === strtolower($method)) {
			$uri = $this->request->uri;
			$uri .= substr($uri, -1) != '/' ? '/' : '';
			
			if(preg_match("#^$url$#i", $uri, $matches)) {
				
				$this->request->params = array_slice($matches, 1);
				// Trigger event mb:route:before and send Request instance to it.
				$this->events->trigger("mb:route:before", array($this->request));
				
				// Prepare , check if we intercepted the response.
				if ($resp = $call->getBeforeResponseIfAny() ) {
					return $resp;
				} else { // Or execute the real call.
					$resp = $call->execute();
				}
				
				
				$this->events->trigger("mb:route:after", array($this->request, $resp));
				
				return true;
			}
			
			
		}
		return false;
	}
	
	
	/**
	 * Registers a new plugin.
	 * After registered, it can be accessed with `$this->my_name`
	 * @param string $name Must contain any of [a-zA-Z0-9_]
	 * @param callback $call A anonymous functon that returns the result of the plugin. 
	 */
	public function plugin($name, $call){
		$this->plugins[$name] = array($call, false);
	}
	
	
	public function initPlugins (array $plugins) {
		$i = 0;
		foreach ($plugins as $p => $config) {
			
			$this->plugin($p, function () use ($p, $config, $i) {
				if (class_exists($p)) {
					$cls = new $p($config);
					if (!($cls instanceof Plugin)) {
						throw new \Exception("Plugin [$p] must extend Minibase\Plugin\Plugin abstract class.");
					}
					// DI $MB
					$cls->setApp($this);
					// Run setup.
					$cls->setup();
					return $cls;
				} else {
					throw new \Exception("Plugin [$i] must be callable or a class extending Minibase\Plugin\Plugin.");
				}	
			});
			// Start.
			$this->$p->start();
			
			$i++;
		}
	}
	
	
	public function __get($name){
		return $this->get($name);
	}
	
	/**
	 * Returns a plugin if it exists, throws Exception otherwise.
	 * If it's already initialized it returns the cached result.
	 *
	 * @param string $name Plugin name
	 * @throws Exception
	 */
	public function get ($name) {
		if (!isset($this->plugins[$name])){
			throw new \Exception("Plugin {$name} does not exist. ");
		}
		list($call, $initialized) = $this->plugins[$name];
		
		if (!$initialized){
			$call = \Closure::bind($call, $this);
				
			$this->plugins[$name][0] = $call();
			$this->plugins[$name][1] = true;
		}
		return $this->plugins[$name][0];
	}
	
	
	public function setConfig($key, $value) {
		$this->cfg[$key] = $value;
		return $this;
	}
	
	/**
	 * Starts the Minibase Application (starts handeling routes)
	 */
	public function start () {
		if (!$this->console) {
			$this->events->trigger("mb:start", [$this]);
			foreach($this->routes as $route){
				list($method, $uri, $call) = $route;
				if ($this->executeRoute($method, $uri, $call)) {
			
					// Unbind flash scope
					if (isset($_SESSION) && isset($_SESSION['flash_msg'])) {
						unset($_SESSION['flash_msg']);
					}
			
					return;
				}
			}
			
			
			// 404 - No route found.
			$this->executeCall($this->events->trigger(
					'mb:exception:RouteNotFoundException',
					array($this->request),
					function () {
					return function () {
						throw new RouteNotFoundException("Could not find route for {$this->request->method} {$this->request->uri}. Catch event mb:exception:RouteNotFoundException to handle this error.");
					};
			}
			)[0]);
		} else {
			$this->console->run();
		}
	}
	
	
	public function getCall ($call) {
		if (is_object($call) && $call instanceof Call) {
		} else if (is_callable($call)) {
			$call = new Call($call);
			$call->setMB($this);
		}
		return $call;
	}
	
	/**
	 * Executes a Call object or a closure.
	 * If it's a closure it gets wrapped in a Call object before dispatched.
	 * @param mixed $call Array($object, method) or Closure
	 */
	public function executeCall ($call) {
		$call = $this->getCall($call);
		return $call->execute();
	}
	
	/**
	 * Loads a router file that includes instructions in JSON format for routing.
	 * @param string $filePath Full file path to the JSON file.
	 */
	public function loadRouteFile ($filePath) {
		$routeParser = RouteParser::fromFile($filePath, $this);
		$routeParser->parse();
	}
	
	/**
	 * Loads a configuration JSON file into MB.
	 * The config file can load plugins, event collections, route files, set base view path and MORE.
	 * @param string $appJsonConfigFile The json file to load.
	 * @param string $appDir The app root dir. For paths in JSON to have a relative path. If null, no base path is set and full paths must be set in the JSON file.
	 */
	public function loadConfigFile ($appJsonConfigFile, $appDir) {
		MBConfigurationParser::fromFile($appJsonConfigFile, $this, $appDir)->parse();
		return $this;
	}
	
	/**
	 * Sets the cache Driver.
	 * Only works if in production, else ArrayCache is used.
	 * @param IArrayCacheConfigure $cacher Instance of a ICache driver.
	 * @param array $config Array of configuration delivered to the cache driver
	 */
	public function configureCacheDriver (IArrayCacheConfigure $cacher, $config = array()) {
		if (!$this->isDevelopment()) {
			$cache = new $cacher();
			$cache->setup($config);
			$this->cache = $cache->getDriver();
		}
	}
	
	/**
	 * Sets the cache driver.
	 * @param CacheProvider $cache
	 */
	public function setCache (CacheProvider $cache) {
		$this->cache = $cache;
	}
	
	
	/**
	 * Adds a new EventCollection
	 * @param \Minibase\Wreqr\EventCollection $collection Instance of a event collection.
	 * @throws \Exception
	 */
	public function addEventCollection (EventCollection $collection) {
		$collection->setMB($this);
		$this->events->addEventCollection($collection);
	}
	
	public function isProduction () {
		return $this->applicationEnv === 'production';
	}
	
	public function isDevelopment () {
		return $this->applicationEnv === 'development';
	}
	
}
