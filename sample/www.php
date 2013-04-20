<?php
/**
 * All the routes and controller callbacks are defined here.
 */

// Require the app so we get the $app instance.
require __DIR__ . '/configure.php';

// Creates a new app object.
$mb = Minibase\MB::create();
configureMBApp($mb);

// Homepage.
$mb->route('get', '/', function () {

	return $this->respond("html")->view(__DIR__ . "/views/homepage.html.php");
});
