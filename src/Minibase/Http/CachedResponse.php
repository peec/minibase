<?php
namespace Minibase\Http;

class CachedResponse extends Response{
	
	public function __construct (array $headers, $body, $statusCode) {
		$this->headers = $headers;
		$this->body  = $body;
		$this->statusCode = $statusCode;
	}
}