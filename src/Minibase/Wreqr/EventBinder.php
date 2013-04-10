<?php
namespace Minibase\Wreqr;


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
