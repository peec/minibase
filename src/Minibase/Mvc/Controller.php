<?php
namespace Minibase\Mvc;


use Minibase\MB;
use Minibase\Http;


abstract class Controller {
	/**
	 * MB app instance.
	 * @var Minibase\MB
	 */
	public $mb;
	
	/**
	 * Request
	 * @var Minibase\Http\Request
	 */
	public $request;
	
	
	/**
	 * Events
	 * @var Minibase\Wreqr\EventBinder
	 */
	public $events;
	
	/**
	 * Translator
	 * @var Minibase\I18n\I18nGetText
	 */
	public $trans;
	
	final public function setMB (MB $mb) {
		$this->mb = $mb;
		$this->request = $mb->request;
		$this->events = $mb->events;
		$this->trans = $mb->trans;
	}
	
	
	
	public function __get($name){
		return $this->mb->get($name);
	}
	
	
	
	/**
	 * Gets a call based on reverse key
	 *
	 * @param string $reverseKey The key to reverse. If routing file is used use Controller.method syntax. Else use supplied $reverseKey.
	 * @return Minibase\Mvc\Call A Call object, you may use reverse() on the call to get the URL for the call.
	 */
	public function & call ($reverseKey) {
		return $this->mb->call($reverseKey);
	}
	
	/**
	 * Renders a view and returns the result.
	 * @param string $view The view file
	 * @param array $vars Variables to add to the view.
	 * @return string The content of the view rendered.
	 */
	public function renderView ($view, $vars = array()) {
		$v = new View($this->events, null, $this->mb->cfg[MB::CFG_VIEWPATH]);
		$v->setRequest($this->request);
		$v->setMB($this->mb);
		return $v->render($view, $vars);
	}
	
	
	/**
	 * Returns a Minibase\Http\Response object based on the type you want to return.
	 * @param string $type The type of response you want, avaialble is: html, redirect and json.
	 * @throws \Exception If the type does not match any of the available types.
	 * @return Minibase\Http\Response
	 */
	public function respond($type = 'html'){
	
		$map = array(
		'json' => function () {
			return new Http\JsonResponse();
		},
		'redirect' => function () {
			return new Http\RedirectResponse();
		},
		'html' => function () {
			$viewPath = isset($this->cfg[MB::CFG_VIEWPATH]) ? $this->cfg[MB::CFG_VIEWPATH] : null;
			return new Http\HtmlResponse($viewPath, $this);
		},
		'text' => function () {
			return new Http\TextResponse();
		}
		);
	
		// Trigger event so it's possible to add more response types.
		$this->mb->events->trigger('mb:respond:before', array(&$map));
	
	
		if (!isset($map[$type])){
			throw new \Exception("No such response ($type).");
		}
		
		// Bind $this to closure.
		$resp = $map[$type]->bindTo($this->mb);
	
		// Get additional args.
		$args = array_slice(func_get_args(), 1);
	
		// Call it.
		$response = call_user_func_array($resp, $args);
		if (!($response instanceof Http\Response)) {
			throw new \Exception("Response of $type must return instance of Minibase\Http\Response.");
		}
		// DI events.
		$response->setEvents($this->events);
		$response->setRequest($this->request);
	
		return $response;
	}
	
}