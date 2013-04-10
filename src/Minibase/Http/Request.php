<?php
namespace Minibase\Http;


class Request {
	public $uri;
	public $method;

	static public function createFromGlobals () {
		$req = new Request();
		$req->method = strtolower($_SERVER['REQUEST_METHOD']);
		$req->uri = $_SERVER['QUERY_STRING'] ?: (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/');
		return $req;
	}

}
