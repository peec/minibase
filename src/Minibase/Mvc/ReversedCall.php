<?php
namespace Minibase\Mvc;

use Minibase\Http\Request;

class ReversedCall {
	
	public $url;
	private $urlComponent;
	private $request;
	
	public function __construct ($url, $urlComponent, Request $request) {
		$this->url = $url;
		$this->urlComponent = $urlComponent;
		$this->request = $request;
	}
	
	
	public function __toString () {
		return $this->url;
	}
	
	/**
	 * Returns if the reversed call is active.
	 */
	public function isActive () {
		return ($this->request->uri === $this->urlComponent);
	}
	
	
}