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
	private $eventCollections = array();

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
	 * Checks if a binding has a on handler
	 * @param string $event Event name.
	 * @return boolean true if $event has a on handler, false otherwise.
	 */
	public function hasOn ($event) {
		return isset($this->bindings[$event]);
	}

	/**
	 * Triggers an event, that can be catched with $this->on.
	 * @param string $event Event name
	 * @param array $args Array of arguments to pass to the callback.
	 * @param callback $callbackIfNoListeners Callback to run if no listeners has been set up.
	 */
	public function trigger ($event, $args = array(), $callbackIfNoListeners = null) {

		$results = array();
			
		if (isset($this->bindings[$event])) {
			foreach ($this->bindings[$event] as $k => $v) {
				list($eCall, $eThat) = $v;
				
				if ($eThat !== null) {
					$eCall = \Closure::bind($eCall, $eThat);
				}
				$results[] = call_user_func_array($eCall, $args);
			}
		}
		if (empty($results) && $callbackIfNoListeners !== null) {
			$results[] = call_user_func_array($callbackIfNoListeners, $args);;
		}
		
		return $results;
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
	
	/**
	 * Adds a new EventCollection
	 * @param EventCollection $collection Instance of a event collection.
	 * @throws \Exception
	 */
	public function addEventCollection(EventCollection $collection) {
		$name = get_class($collection);
		if (isset($this->eventCollections[$name])) {
			throw new \Exception ("EventCollection $name is already added. Must not add same collection twice.");
		}
		// Bind all events.
		$collection->bindAll();
		$this->eventCollections[$name] = $collection;
		return $collection;
	}
	
	/**
	 * @param string $className The class name.
	 * @return \Minibase\Wreqr\EventCollection
	 */
	public function getEventCollection ($className) {
		return $this->eventCollections[$className];
	}
	

}
