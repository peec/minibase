<?php
namespace Minibase\Http;

/**
 * Return this when you want to redirect the user to anywhere.
 * @author peec
 *
 */
class RedirectResponse extends Response {
	/**
	 * Sets where to redirect to.
	 * @param string $location The location to redirect to.
	 * @return Minibase\Http\RedirectResponse
	 */
	public function to ($location) {
		$this->headers["Location"] = $location;
		return $this;
	}
	
	/**
	 * Redirects to internal site. Using the base URL before $location.
	 * @param unknown_type $location
	 */
	public function toThis ($location) {
		$dr = $_SERVER['DOCUMENT_ROOT'];
		$path = dirname($_SERVER['SCRIPT_FILENAME']);
		$base = substr($path, strlen($dr));
		return $this->to("{$base}/{$location}");
	}

	public function after () {
		die();
	}

}
