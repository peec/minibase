<?php
namespace Minibase\Mvc;


use Minibase\MB;

abstract class Controller {
	/**
	 * 
	 * @var Minibase\MB
	 */
	public $mb;
	
	final public function setMB (MB $mb) {
		$this->mb = $mb;
	}
	
	/**
	 * Shortcut for `$this->mb->respond`.
	 * 
	 * @param string $view
	 */
	final public function respond ($view) {
		return call_user_func_array(array($this->mb, 'respond'), func_get_args());
	}
	
	
}