<?php
namespace Minibase\Http;

use Minibase\Mvc\View;

/**
 * Returns a HTML response.
 * @author peec
 *
 */
class HtmlResponse extends Response{
	private $viewPath; 

	public function __construct($viewPath) {
		$this->viewPath = $viewPath;
	}
	/**
	 * The view file to use as the response.
	 * @param string $view The view file location.
	 * @param array $vars Array of arguments.
	 * @return Minibase\Http\HtmlResponse
	 */
	public function view ($view, $vars = array()) {
		$v = new View($this->events, null, $this->viewPath);
		$this->body = $v->render($view, $vars);

		return $this;
	}
}

