<?php
namespace Minibase\Http;

use Minibase\Wreqr\EventBinder;

/**
 * Returns a text response.
 * @author peec
 *
 */
class TextResponse extends Response {

	public function __construct () {
		$this->headers["content-type"] = "text/plain";
	}

	/**
	 * The data to return as JSON. 
	 * @param mixed $data Object or array of data to encode to json.
	 * @return Minibase\Http\TextResponse
	 */
	public function text ($data) {
		$this->body = $data;
		return $this;
	}


}
