[![Build Status](https://travis-ci.org/peec/minibase.png?branch=master)](https://travis-ci.org/peec/minibase)


## Minibase

Minibase is a small framework for RESTful PHP applications. Minibase is perfect to create API's for JS frameworks such as `Backbone`. It's also great to create small sites. Minibase provides you with some arcitecture, but doesn't enforce you to use any 3rdparty library. You can use the ORM, Logger, Template engine of your choice with minimal configuration due to the plugin system.

Minibase takes use of PHP 5.4 features such as Closure scope binding, so PHP 5.4+ is a must.

### Install

You can install Minibase with Composer.

1. Install composer.
2. Create composer.json and put a require dependency.

```json
{
    "require": {
        "minibase/minibase": "dev-master"
    }
}
```

3. Run `composer install`


### Simplicity

- No database ORM / database layer built in.
- No template engine.
- Almost no learning curve.
- Use the libraries you want, ie. Doctrine, Smarty, etc.
- Event based architecture. Hook into any functionality of Minibase and add behavoir as your go!


### Routing.

There are no built in router configuration, it's neatly wrapped with a method that takes 3 arguments. HTTP method, uri and callback. 


```php
	// Create the app object.
	$mb = \Minibase\MB::create();
	
	// Home page.
	$mb->route("get", "/", function () {
		// some logic.
		// And return.
		return $this->respond("html")->view("views/test.html.php");
	});



	// Some JSON API.
	$mb->route("get", "/api/news/list", function () {
		// some logic.

		return $this->respond("json")->data(array("hello", "world"));
	});


	// Redirect to google
	$mb->route("get", "/i-want-to-google", function () {
		// some logic.

		return $this->respond("redirect")->to("http://google.com");
	});
```

### Extensible.

Minibase has a simple plugin system whereas you can create global plugins and register plugins simply. Dependency injected, so only initialized once (thereby performance boost).

```php
	// Create.
	$mb->plugin("my_plugin", function () {
		return new Something();
	});

	// ... and later on , use.
	$mb->my_plugin->someMethodInSomethingClass();
```


### Event based architecture.

Minibase uses event based architecture with a simple on / trigger system.

Forexample you can listen on event on before route.

```php
	$mb->events->on("mb:route:before", function ($uri, $method, $params){
		// Run some code right before we run the "controller method".
	});
```

You can also trigger custom events.

```php
	$mb->events->trigger("hello", array("World!"));
```

And listen to your event...

```php
	$mb->events->on("hello", function ($arg1) {
		echo "Hello  $arg1";
	});
```

