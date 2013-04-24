<?php
namespace Minibase\Http;

/**
 * A HTTP Request wrapper.
 * 
 * @author peec
 *
 */
use Minibase\MB;

use Minibase\Wreqr\EventBinder;

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
	 * Has the script name, it doesn't nessecary need to be index.php.
	 * @var string the Script name, eg. index.php
	 */
	public $scriptName;
	

	public $isRewriteEnabled = false;
	
	
	/**
	 * 
	 * @var Minibase\MB
	 */
	private $mb;
	
	/**
	 * 
	 * @var string Returns the protocol + hostname. eg. https://localhost or http://mydomain.com
	 */
	public $domain;
	
	/**
	 * Returns a Request object from the global $_SERVER vars.
	 * @return Minibase\Http\Request
	 */
	static public function createFromGlobals () {
		$req = new Request();
		
		// Check if mod_rewrite is enabled.
		$req->isRewriteEnabled = isset($_SERVER['HTTP_MOD_REWRITE']) && $_SERVER['HTTP_MOD_REWRITE'] === 'On' ? true : false;
		
		
		// Set HTTP METHOD.
		$req->method = strtolower($_SERVER['REQUEST_METHOD']);
		
		///// SET URI
		
		// Normal path info
		$uri = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : null; // Normal no redirect.
		// htaccess rewrite and such.
		if ($uri === null) {
			$uri = isset($_SERVER['REDIRECT_QUERY_STRING']) ? $_SERVER['REDIRECT_QUERY_STRING'] : null;
		}
		$req->uri = $uri ?: '/';
		
		// Set base path.
		$base = dirname($_SERVER['SCRIPT_NAME']);
		$base =  (substr($base, 0, 1) == '/' ? '' : '/') . ($base ? $base : '');
		if (strlen($base) > 1 && substr($base, strlen($base)-1) != '/') {
			$base .= '/';
		}
		$req->basePath = $base;
		
		// Set http is secure
		$req->isSecure =  isset($_SERVER["HTTPS"]) ? true : false;
		
		// Set the script name
		$req->scriptName = basename($_SERVER['SCRIPT_FILENAME']);
		
		$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
		$req->domain = ($req->isSecure ? 'https://' : 'http://') . ($host ?: $_SERVER['SERVER_NAME']);
		
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
		if ($json === null){
			$this->mb->executeCall($this->mb->events->trigger("mb:exception:InvalidJsonRequestException", array($this), function () {
				return function () {
					throw new InvalidJsonRequestException("Invalid JSON request. Catch mb:error:InvalidJsonRequestException event to customize this error event.");
				};
			})[0]);
			die();
		}
		return $json;
	}
	
	
	public function setMB (MB $mb) {
		$this->mb = $mb;
	}
	
}
