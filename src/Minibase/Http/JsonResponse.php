<?php
namespace Minibase\Http;

use Minibase\Wreqr\EventBinder;

class JsonResponse extends Response {

	public function __construct (EventBinder $events) {
		parent::__construct($events);
		$this->headers["content-type"] = "application/json";
	}

	public function data ($data) {
		$this->body = json_encode($data);
		return $this;
	}


}
