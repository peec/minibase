<?php
namespace Minibase\Wreqr;

/**
 * EventBinder can bind and trigger events.
 * 
 * @author peec
 *
 */
class EventBinder {
	private $bindings = array();

	/**
	 * Run a callback once event $event is triggered.
	 * @param string $event The event name
	 * @param callback $callback The callback to run.
	 * @param object $that What scope ($this) to use inside the callback.
	 */
	public function on ($event, $callback, $that = null) {
		$this->bindings[$event][] = array($callback, $that);
	}

	/**
	 * Triggers an event, that can be catched with $this->on.
	 * @param string $event Event name
	 * @param array $args Array of arguments to pass to the callback.
	 */
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

	/**
	 * Removes event callback from the eventbinder.
	 * @param string $event The event name
	 * @param callback $callback The callback that should be unbound.
	 */
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
