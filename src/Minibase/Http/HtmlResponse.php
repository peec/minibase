<?php
namespace Minibase\Http;

use Minibase\Mvc\View;

class HtmlResponse extends Response{

	public function view ($view, $vars = array()) {
		$v = new View($this->events);
		$this->body = $v->render($view, $vars);

		return $this;
	}
}

