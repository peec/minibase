<?php
namespace app\controllers;

use Minibase\Mvc\Controller;

class Home extends Controller {
	
	public function index () {
		
		return $this->respond("html")
			->view('home.html');
	}
	
}