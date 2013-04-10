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
	public $params = array();
	public $events;
	public $request;
	
	static public function create () {
		$mb = new MB();
		$mb->events = new EventBinder();
		$mb->request = Http\Request::createFromGlobals();
		return $mb;
	}
	
	public function on ($method, $url, $call) {
		
		if ($this->request->method === strtolower($method)) {
			$uri = $this->request->uri;
			
			if(preg_match("#^$url$#i", $uri, $matches)) {
				$this->params = array_slice($matches, 1);
				$call = \Closure::bind($call, $this);
				
				$this->events->trigger("mb:route:before", [$uri, $method, $this->params]);
				$resp = $call();
				if (!($resp instanceof Response)){
					throw new InvalidControllerReturnException("Controllers must return instances of a Response.");
				}
				$resp->execute();
				
				$this->events->trigger("mb:route:after", [$uri, $this->params]);
			}
			
			
		}
		return $this;
	}
	
	
	public function plugin($name, $call){
		$this->plugins[$name] = array($call, false);
	}
	
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
