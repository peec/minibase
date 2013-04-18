<?php
namespace Minibase\Mvc\RouteParser;

use Minibase\MB;
/**
 * Makes it possible to define a JSON file containg routes.
 * Sample:
 * [
 * 		["get", "/", "MyController.index"]
 * ]
 * @author peec
 *
 */
class RouteParser {
	private $routeConfig;
	private $mb;
	
	/**
	 * 
	 * @param string $file Path to the JSON file that your want to load.
	 * @param Minibase\MB $mb
	 */
	static public function fromFile ($file, MB $mb) {
		return new RouteParser(file_get_contents($file), $mb);
	}
	
	public function __construct ($json, MB $mb) {
		$jsonData = json_decode($json);
		if ($jsonData === null) {
			throw new RouteParseJsonException("Could not parse router file (Not valid JSON):\n {$json}");
		}
		$this->routeConfig = $jsonData;
		$this->mb = $mb;
	}
		
	/**
	 * Parses the route file and adds additional route handlers to the global router.
	 */
	public function parse () {
		foreach($this->routeConfig as $route) {
			$bits = explode('.', $route[2]);
			list ($controller, $method) = $bits;
			$this->mb->route($route[0], $route[1], array($controller, $method));
		}
	}
	
	
	
}