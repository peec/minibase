<?php
namespace Minibase\Http;

use Minibase\Wreqr\EventBinder;

abstract class Response{
	public $headers = array();

	public $events;
	public $statusCode = 200;

	public $body = null;

	public function __construct(EventBinder $events){
		$this->events = $events;
	}

	public function after () {

	}

	public function execute () {
		foreach($this->headers as $name => $val) {
			header("{$name}: {$val}");
		}
		http_response_code($this->statusCode);
		if ($this->body !== null){
			echo $this->body;
		}
		$this->after();
	}

	public function with ($statusCode = 200) {
		$this->statusCode = $statusCode;
		return $this;
	}

	public function asType ($contentType) {
		$this->headers['content-type'] = $contentType;
		return $this;
	}


}