[![Build Status](https://travis-ci.org/peec/minibase.png?branch=master)](https://travis-ci.org/peec/minibase)


## Minibase

Minibase is a small framework for RESTful PHP applications.

It uses PHP 5.4 features, so PHP 5.4+ is supported.



### Simplicity

- No database ORM / database layer built in.
- No template engine.
- Almost no learning curve.
- Use the libraries you want, ie. Doctrine, Smarty, etc.
- Event based architecture.


### Routing.

There are no built in router configuration, it's neatly wrapped with a method that takes 3 arguments. HTTP method, uri and callback. 


	// Home page.
	$mb->on("get", "/", function () {
		// some logic.
		// And return.
		return $this->respond("html")->view("views/test.html.php");
	});



	// Some JSON API.
	$mb->on("get", "/api/news/list", function () {
		// some logic.

		return $this->respond("json")->data(array("hello", "world"));
	});


	// Redirect to google
	$mb->on("get", "/i-want-to-google", function () {
		// some logic.

		return $this->respond("redirect")->to("http://google.com");
	});


### Extensible.

Minibase has a simple plugin system whereas you can create global plugins and register plugins simply. Dependency injected, so only initialized once (thereby performance boost).

	// Create.
	$mb->plugin("my_plugin", function () {
		return new Something();
	});

	// ... and later on , use.
	$mb->my_plugin->someMethodInSomethingClass();


