<?php
namespace Minibase\Http;

use Minibase\Wreqr\EventBinder;

/**
 * Returns a json response.
 * @author peec
 *
 */
class JsonResponse extends Response {

	public function __construct () {
		$this->headers["content-type"] = "application/json";
	}

	/**
	 * The data to return as JSON. 
	 * @param mixed $data Object or array of data to encode to json.
	 * @return Minibase\Http\JsonResponse
	 */
	public function data ($data) {
		$this->body = json_encode($data);
		return $this;
	}


}
