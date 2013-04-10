<?php
namespace Minibase\Http;

/**
 * A HTTP Request wrapper.
 * 
 * @author peec
 *
 */
class Request {
	/**
	 * 
	 * @var string The URI (eg. /hello/world or just "/")
	 */
	public $uri;
	/**
	 * 
	 * @var string The http request method, i.e. post, get .etc.
	 */
	public $method;

	/**
	 * Returns a Request object from the global $_SERVER vars.
	 * @return Minibase\Http\Request
	 */
	static public function createFromGlobals () {
		$req = new Request();
		$req->method = strtolower($_SERVER['REQUEST_METHOD']);
		$req->uri = $_SERVER['QUERY_STRING'] ?: (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/');
		return $req;
	}

}
