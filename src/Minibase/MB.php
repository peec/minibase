<?php
namespace Minibase;

class MB{
	
	private $plugins = array();
	public $params = array();
	public $events;
	
	static public function create () {
		$mb = new MB();
		$mb->events = new EventBinder();
		$mb->request = Request::createFromGlobals();
		return $mb;
	}
	
	public function on ($method, $url, $call) {
		
		if ($this->request->method === strtolower($method)) {
			$uri = $this->request->uri;
			
			if(preg_match("#^$url$#i", $uri, $matches)) {
				$this->params = array_slice($matches, 1);
				$call = \Closure::bind($call, $this);
				
				$this->events->trigger("mb:route:before", [$uri, $method, $this->params]);
				$call()->execute();
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
				return new JsonResponse($this->events);
				break;
			case "redirect":
				return new RedirectResponse($this->events);
				break;
			case "html":
				return new HtmlResponse($this->events);
				break;
			default: 
				throw new Exception("No such response type.");
				break;
		}
	}
		
}

class JsonResponse extends Response {
	
	public function __construct (EventBinder $events) {
		parent::__construct($events);
		$this->headers["content-type"] = "application/json";
	}
	
	public function data ($data) {
		$this->body = json_encode($data);
		return $this;	
	}
	
	
}

class RedirectResponse extends Response {
	public function to ($location) {
		$this->headers["Location"] = $location;
		return $this;
	}
	
	public function after () {
		die();
	}
	
}

class HtmlResponse extends Response{
		
	public function view ($view, $vars = array()) {
		$v = new View($this->events);
		$this->body = $v->render($view, $vars);
		
		return $this;
	}
}

abstract class Response{
	public $headers = array();
	
	public $events;
	public $statusCode = 200;
	
	public $body = null;
	
	public function __construct(EventBinder $events){
		$this->events = $events;
	}
	
	public function after () {
		
	}
	
	public function execute () {
		foreach($this->headers as $name => $val) {
			header("{$name}: {$val}");
		}
		http_response_code($this->statusCode);
		if ($this->body !== null){
			echo $this->body;
		}
		$this->after();
	}
	
	public function with ($statusCode = 200) {
		$this->statusCode = $statusCode;
		return $this;
	}
	
	public function asType ($contentType) {
		$this->headers['content-type'] = $contentType;
		return $this;
	}
	
	
}

class View{
	public $parentView;
	public $request;
	public $events;
	
	public function __construct(EventBinder $eventbinder, $parentView = null) {
		$this->parentView = $parentView;
		$this->events = $eventbinder;
	}
	public function e ($str) {
		echo $this->escape($str);
	}
	
	
	public function escape ($str) {
		return htmlentities($str, ENT_QUOTES, 'utf-8');
	}
	
	public function import ($view, $vars = array()) {
		$v = new View($this->events, $this);
		echo $v->render($view, $vars);
	}
	
	public function render ($view, $vars = array()) {
		
		$callback = function($vars) use ($view) {
			$this->events->trigger("before:render", [$view, &$vars]);
			extract($vars);
			
			include $view;
		};
		
		ob_start();		
		$call = \Closure::bind($callback, $this);
		$call($vars);
		
		$content = ob_get_clean();
		$this->events->trigger("after:render", [$view, $content]);
		return $content;
	}
	
}


class EventBinder {
	private $bindings = array();
		
	public function on ($event, $callback, $that = null) {
		$this->bindings[$event][] = array($callback, $that);
	}	
	
	public function trigger ($event, $args = array()) {
		if (!isset($this->bindings[$event])) return;
		
		foreach ($this->bindings[$event] as $k => $v) {
			list($eCall, $eThat) = $v;
			if ($eThat !== null) {
				$eCall = \Closure::bind($eCall, $eThat);
			}
			call_user_func_array($eCall, $args);
		}
	}
	
	public function off ($event, $callback) {
		if (!isset($this->bindings[$event])) return;	
		foreach ($this->bindings[$event] as $k => $v) {
			list($eCall, $eThat) = $v;
			if ($eCall === $callback) {
				unset($this->bindings[$event][$k]);
			}
		}
	}
	
}

class Request {
	public $uri;
	public $method;
	
	static public function createFromGlobals () {
		$req = new Request();
		$req->method = strtolower($_SERVER['REQUEST_METHOD']);
		$req->uri = $_SERVER['QUERY_STRING'] ?: (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/');
		return $req;
	}
	
}
