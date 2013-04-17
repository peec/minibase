<?php
namespace Minibase;

use Minibase\Plugin\Plugin;

use Minibase\Http\Response;

use Minibase\Http\Request;

use Minibase\Wreqr\EventBinder;
use Minibase\Http;

/**
 * Application stub for a simple application.
 * 
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
	private $cfg = array();
	
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
		return $mb;
	}
	
	/**
	 * Routes HTTP requests against closure callbacks.
	 * 
	 * @param string $method The Request method (get,post,put,delete,etc..)
	 * @param string $url The url starting with backslash, ie. "/" or "/hello/(\d+)". Can have regexp.
	 * @param string $call A callback (closure) to run if the url and http method matches.
	 * @return Minibase\MB
	 * @throws InvalidControllerReturnException
	 */
	public function route ($method, $url, $call) {
		array_push($this->routes, array($method, $url, $call));
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
				
				$resp = $this->executeCall($call);
				
				$this->events->trigger("mb:route:after", array($this->request, $resp));
				
				return true;
			}
			
			
		}
		return false;
	}
	
	public function executeCall ($call) {
		// if not array (obj, method) or function call, bind $this.
		if (!is_array($call) && !is_string($call)){
			$call = \Closure::bind($call, $this);
		}
		
		try {
			$resp = call_user_func_array($call, array($this->request->params, $this));
				
		} catch (Http\InvalidJsonRequestException $e) {
			if (!$this->events->hasOn("mb:error:400")){
				throw $e;
			} else {
				$resp = $this->events->trigger("mb:error:400", array($e))[0];
			}
		}
		
		
		if (!($resp instanceof Response)){
			throw new InvalidControllerReturnException("Controllers must return instances of a Response.");
		}
		$resp->execute();
		return $resp;
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
			throw new Exception("Plugin {$name} does not exist. ");
		}
		list($call, $initialized) = $this->plugins[$name];
		
		if (!$initialized){
			$call = \Closure::bind($call, $this);
				
			$this->plugins[$name][0] = $call();
			$this->plugins[$name][1] = true;
		}
		return $this->plugins[$name][0];
	}
	
	/**
	 * Returns a Minibase\Http\Response object based on the type you want to return.
	 * @param string $type The type of response you want, avaialble is: html, redirect and json.
	 * @throws \Exception If the type does not match any of the available types.
	 * @return Minibase\Http\Response
	 */
	public function respond($type = 'html'){
		
		$map = array(
			'json' => function () {
				return new Http\JsonResponse();
			},
			'redirect' => function () {
				return new Http\RedirectResponse();
			},
			'html' => function () {
				$viewPath = isset($this->cfg[self::CFG_VIEWPATH]) ? $this->cfg[self::CFG_VIEWPATH] : null;
				return new Http\HtmlResponse($viewPath);
			}
		);
		
		// Trigger event so it's possible to add more response types.
		$this->events->trigger('mb:respond:before', array(&$map));
		
		
		if (!isset($map[$type])){
			throw new \Exception("No such response ($type).");
		}
		// Bind $this to closure.
		$resp = $map[$type]->bindTo($this);
		
		// Get additional args.
		$args = array_slice(func_get_args(), 1);
		
		// Call it.
		$response = call_user_func_array($resp, $args);
		if (!($response instanceof Http\Response)) {
			throw new \Exception("Response of $type must return instance of Minibase\Http\Response.");
		}
		// DI events.
		$response->setEvents($this->events);
		
		return $response;
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
}
