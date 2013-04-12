<?php
/**
 * All the routes and controller callbacks are defined here.
 */

// Require the app so we get the $app instance.
require __DIR__ . '/app.php';

// Homepage.
$app->route('get', '/', function () {

	return $this->respond("html")->view(__DIR__ . "/views/homepage.html.php");
});
