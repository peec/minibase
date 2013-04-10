<?php
namespace Minibase\Http;

class RedirectResponse extends Response {
	public function to ($location) {
		$this->headers["Location"] = $location;
		return $this;
	}

	public function after () {
		die();
	}

}
