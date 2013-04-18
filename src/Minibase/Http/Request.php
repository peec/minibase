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
	 * 
	 * @var boolean TRUE if HTTPS, FALSE otherwise.
	 */
	public $isSecure;
	
	/**
	 * 
	 * @var array Array of arguments to this uri.
	 */
	public $params;
	
	/**
	 * If url is forexample http://localhost/myapp/index.php , basePath is set to /myapp
	 * @var string the base path to the script currently running.
	 */
	public $basePath;
	

	/**
	 * Returns a Request object from the global $_SERVER vars.
	 * @return Minibase\Http\Request
	 */
	static public function createFromGlobals () {
		$req = new Request();
		
		// Set HTTP METHOD.
		$req->method = strtolower($_SERVER['REQUEST_METHOD']);
		
		// Set URI
		$uri = substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME']));
		$req->uri = $uri ?: '/';
		
		// Set base path.
		$base = dirname($_SERVER['SCRIPT_NAME']);
		$base =  (substr($base, 0, 1) == '/' ? '' : '/') . ($base ? $base : '');
		if (strlen($base) > 1 && substr($base, strlen($base)-1) != '/') {
			$base .= '/';
		}
		$req->basePath = $base;
		
		
		$req->isSecure =  isset($_SERVER["HTTPS"]) ? true : false;
		return $req;
	}
	
	
	
	
	/**
	 * @return string Returns the raw HTTP request body as a string.
	 */
	public function raw () {
		return file_get_contents('php://input');
	}

	/**
	 * Useful for JSON API's.
	 * @return mixed Returns the HTTP request body as JSON decoded to array.
	 * @throws Minibase\Http\InvalidJsonRequestException if json string is malformated.
	 */
	public function json () {
		$json = json_decode($this->raw());
		if (!$json){
			throw new InvalidJsonRequestException("Invalid JSON request.");
		}	
		return $json;
	}
	
}
