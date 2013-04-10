<?php
namespace Minibase\Mvc;

use Minibase\Wreqr\EventBinder;

/**
 * A View model. Can render views with scope of the following view.
 * $this in template files will refer to a instance of this View.
 * 
 * @author peec
 *
 */
class View{
	public $parentView;
	public $request;
	public $events;

	public function __construct(EventBinder $eventbinder, $parentView = null) {
		$this->parentView = $parentView;
		$this->events = $eventbinder;
	}
	
	/**
	 * Echo a XSS stripped version of a string.
	 * @param string $str The string you want to output
	 */
	public function e ($str) {
		echo $this->escape($str);
	}


	/**
	 * Escapes a string with htmlentities.
	 * Prevents XSS injection attacks.
	 * @param string $str The string you want to escape.
	 */
	public function escape ($str) {
		return htmlentities($str, ENT_QUOTES, 'utf-8');
	}

	/**
	 * Imports a new view file where this is called.
	 * @param string_type $view The view file location.
	 * @param array $vars Array of arguments.
	 */
	public function import ($view, $vars = array()) {
		$v = new View($this->events, $this);
		echo $v->render($view, $vars);
	}

	/**
	 * Renders a view file and returns it's content.
	 * @param string $view The view file location
	 * @param array $vars Array of arguments
	 */
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
