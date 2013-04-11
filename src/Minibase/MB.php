<?php
namespace Minibase;

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
	
	private $plugins = array();
	public $events;
	public $request;
	
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
	public function on ($method, $url, $call) {
		
		if ($this->request->method === strtolower($method)) {
			$uri = $this->request->uri;
			
			if(preg_match("#^$url$#i", $uri, $matches)) {
				
				$this->request->params = array_slice($matches, 1);
				// Trigger event mb:route:before and send Request instance to it.
				$this->events->trigger("mb:route:before", array($this->request));
				
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
				
				$this->events->trigger("mb:route:after", array($this->request, $resp));
			}
			
			
		}
		return $this;
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
	
	/**
	 * Returns a plugin if it exists, throws Exception otherwise.
	 * If it's already initialized it returns the cached result.
	 * 
	 * @param string $name Plugin name
	 * @throws Exception
	 */
	public function __get($name){
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
		switch($type) {
			case "json":
				return new Http\JsonResponse($this->events);
				break;
			case "redirect":
				return new Http\RedirectResponse($this->events);
				break;
			case "html":
				return new Http\HtmlResponse($this->events);
				break;
			default: 
				throw new \Exception("No such response type.");
				break;
		}
	}
		
}
