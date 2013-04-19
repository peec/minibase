<?php
namespace Minibase\Mvc;

/**
 * Simple $mb->route callbacks are bound to instance of this.
 * @author peec
 *
 */
class ClosureController extends Controller {
	private $methods = array();
	
	const CALLBACK_NAME = "callback";
	
	public function __construct (callable $callback) {
		$this->addMethod(self::CALLBACK_NAME, $callback);
	}
	
	protected function addMethod ($name, callable $callback) {
		$callback = $callback->bindTo($this);
		$this->methods[$name] = $callback;
	}
	
	public function __call($method, $args) {
		if(is_callable($this->methods[$method])) {
			return call_user_func_array($this->methods[$method], $args);
		}
	}
}