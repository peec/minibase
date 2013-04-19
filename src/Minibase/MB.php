<?php
namespace Minibase;

use Minibase\Cache\ICache;

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
	 * @var string Config key for view path.
	 */
	const CFG_VIEWPATH = "viewPath";
	
	
	
	/**
	 * Factory a new application
	 * Uses global variables to create nested objects.
	 * @return Minibase\MB
	 */
	static public function create () {
		$mb = new MB();
		$mb->events = new EventBinder();
		$mb->request = Http\Request::createFromGlobals();
		$mb->request->setMB($mb);
		return $mb;
	}
	
	public function __construct() {
		AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Annotations.php');
		$this->annotationReader = new AnnotationReader();
		
	}
	
	/**
	 * Routes HTTP requests against closure callbacks.
	 * 
	 * @param string $method The Request method (get,post,put,delete,etc..)
	 * @param string $url The url starting with backslash, ie. "/" or "/hello/(\d+)". Can have regexp.
	 * @param string $call A callback (closure) to run if the url and http method matches.
	 * @param string $reverseKey A reverse route key that is unique to this route. 
	 * @return Minibase\MB
	 * @throws InvalidControllerReturnException
	 */
	public function route ($method, $url, $call, $reverseKey = null) {
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
	public function executeRoute ($method, $url, $call) {
			
		if ($this->request->method === strtolower($method)) {
			$uri = $this->request->uri;
			if(preg_match("#^$url$#i", $uri, $matches)) {
				
				$this->request->params = array_slice($matches, 1);
				// Trigger event mb:route:before and send Request instance to it.
				$this->events->trigger("mb:route:before", array($this->request));
				
				
				$resp = $call->execute();
				
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
					$cls->setApp($this);
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
		
	}
	
	/**
	 * Executes a Call object or a closure.
	 * If it's a closure it gets wrapped in a Call object before dispatched.
	 * @param mixed $call Array($object, method) or Closure
	 */
	public function executeCall ($call) {
		if (is_object($call) && $call instanceof Call) {
			$call->execute();
		} else if (is_callable($call)) {
			$call = new Call($call);
			$call->setMB($this);
			$call->execute();
		} else {
			throw new \Exception ("Call is not callable. Cannot execute call.");
		}
	}
	
	/**
	 * Loads a router file that includes instructions in JSON format for routing.
	 * @param string $filePath Full file path to the JSON file.
	 */
	public function loadRouteFile ($filePath) {
		$routeParser = RouteParser::fromFile($filePath, $this);
		$routeParser->parse();
	}
	
	public function setCacheDriver (ICache $cacher, $config = array()) {
		$this->plugin("cache", function () use ($cacher, $config) {
			$cache = new $cacher();
			$cache->setup($config);
			
			return $cache;
		});
		
	}
}
