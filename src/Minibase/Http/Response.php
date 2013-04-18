<?php
namespace Minibase\Http;

use Minibase\Wreqr\EventBinder;

/**
 * Abstract class for a Response.
 * 
 * @author peec
 *
 */
abstract class Response{
	/**
	 * 
	 * @var array Array of headers, key => value.
	 */
	public $headers = array();

	/**
	 * 
	 * @var Minibase\Wreqr\EventBinder the EventBinder instance.
	 */
	public $events;
	/**
	 * 
	 * @var int The statuscode. Default is 200.
	 */
	public $statusCode = 200;

	/**
	 * 
	 * @var string The body to send to the browser.
	 */
	public $body = null;

	
	public $request;
	
	public function setEvents (EventBinder $events) {
		$this->events = $events;
	}
	
	public function setRequest (Request $request) {
		$this->request = $request;
	}
	

	/**
	 * Runs after respnse has been delivered.
	 */
	public function after () {

	}

	/**
	 * Executes a response object. Echoing it to the browser.
	 */
	public function execute () {
		foreach($this->headers as $name => $val) {
			header("{$name}: {$val}");
		}
		if ($this->body !== null){
			if ($this->statusCode !== 200) {
				http_response_code($this->statusCode);
			}
			
			echo $this->body;
		}
		$this->after();
	}

	/**
	 * Sets the status code.
	 * @param int $statusCode The status code ie 200 , 404 etc.
	 * @return Minibase\Http\Response
	 */
	public function with ($statusCode = 200) {
		$this->statusCode = $statusCode;
		return $this;
	}

	/**
	 * Sets the content-type
	 * @param string $contentType The content type, ie. application/xml
	 * @return Minibase\Http\Response
	 */
	public function asType ($contentType) {
		$this->headers['content-type'] = $contentType;
		return $this;
	}


}