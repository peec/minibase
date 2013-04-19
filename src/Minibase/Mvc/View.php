<?php
namespace Minibase\Mvc;

use Minibase\MB;

use Minibase\Http\Request;

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
	
	private $viewPath;
	
	private $mb;

	public function __construct(EventBinder $eventbinder, $parentView = null, $viewPath = null) {
		$this->parentView = $parentView;
		$this->events = $eventbinder;
		$this->viewPath = $viewPath;
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
		$v = new View($this->events, $this, $this->viewPath);
		$v->setRequest($this->request);
		$v->setMB($this->mb);
		echo $v->render($view, $vars);
	}

	/**
	 * Renders a view file and returns it's content.
	 * @param string $view The view file location
	 * @param array $vars Array of arguments
	 */
	public function render ($view, $vars = array()) {

		if (isset($_SESSION) && isset($_SESSION['flash_msg'])) {
			$vars['flash'] = $_SESSION['flash_msg'];
		}
		
		$viewPath = $this->viewPath;
		
		ob_start();
		
		$this->events->trigger("before:render", [$this, &$vars]);
		$call = $this->events->trigger("mb:render", array(), function() {	
			return function ($vars, $view, $viewPath) {
				extract($vars);
				include ($viewPath ?: "") . $view;
			};
		})[0];
		
		$call = \Closure::bind($call, $this);
		$call($vars, $view, $viewPath);

		
		$content = ob_get_clean();
		$this->events->trigger("after:render", [$view, &$content]);
		return $content;
	}

	public function asset ($resource) {
		echo $this->request->basePath . $resource;
	}
	
	public function call ($call) {
		return $this->mb->call($call);
	}
	
	public function setMB (MB $mb) {
		$this->mb = $mb;
	}
	
	public function setRequest (Request $request) {
		$this->request = $request;
	}
	
	/**
	 * Caches a partial (fragment). 
	 * @param string $key The key
	 * @param callable $block A function that echo stuff.
	 * @param int $expire Expire in seconds, 0 = forever.
	 * @return string The content from cache or just from output. Next time served from cache.
	 */
	public function cache($key, callable $block, $expire = 0) {
		ob_start();
		// Eventuallty filled out.
		$content = "";
		
		$cachedContent = $this->mb->cache->get($key);
		
		if ($cachedContent !== null) {
			$content = $cachedContent;
		} else {
			$block = $block->bindTo($this);
			ob_start();
			$block();
			$content = ob_get_clean();
			$this->mb->cache->set($key, $content, $expire);
		}
		return $content;
	}
	
	
	
	
}
