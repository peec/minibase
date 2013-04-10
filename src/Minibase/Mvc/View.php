<?php
namespace Minibase\Mvc;

use Minibase\Wreqr\EventBinder;


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
