[![Build Status](https://travis-ci.org/peec/minibase.png?branch=master)](https://travis-ci.org/peec/minibase)


## Minibase (PHP 5.4+)

Minibase is a framework for RESTful PHP applications, based on [Event Driven Architecture](http://en.wikipedia.org/wiki/Event-driven_architecture). Minibase is what a framework should do, it gives you a minimum base to extend on as you go. Plugins can be downloaded from anywhere and included easily.

Minibase is **not fullstack**, but with **plugins** it is.


For a sample application see the [Sample Minibase App](https://github.com/peec/minibase-sample). The sample app contains some architecture using best practices and includes some plugins to show the capabilities of Minibase.



#### Configurable

We use `JSON` for configuration files, because its known, easy and good performance. You can create a routes.json file and put your route binding there. You can create a app.json file and put app configuration there. For those who likes the programmatic way of binding these things you don't have to use configuration files.

#### Extensible

Minibase uses event based architecture with a simple on / trigger system. Using Minibase Plugin system along with the Events makes it easy to create standalone plugins.

Event based architecture makes this framework stand out, all kinds of plugins can be created. Also it's easy. Take a look at [**3rdparty plugins**](docs/3rdparty-plugins.md) to see some plugins that are already created for those who are looking for forexample Twig templating engine, and Doctrine ORM support. 


### Install

You can install Minibase with [Composer](http://getcomposer.org/), if you are not using composer, start using it.

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




### Documentation.


* [**Minibase**](docs/minibase.md): Configure the Minibase object.
* [**Routing**](docs/routing.md): See how to do routing, reverse routing and such.
* [**Events**](docs/events.md): Minibase events, listen to these to extend minibase.
* [**Commandline**](docs/command-line.md): Minibase includes symfony CLI, CLI and also be extended with custom commands.
* [**Plugins**](docs/plugins.md): Create your own plugins publish them to composer and share.
* [**3rdparty plugins**](docs/3rdparty-plugins.md): Browse plugins to extend minibase.




### Simple (if you want)

There are no built in router configuration, it's neatly wrapped with a method that takes 3 arguments. HTTP method, uri and callback. Note, there are also OOP way of routing using `Controller.method` approach. See more about [**Routing**](docs/routing.md).


*Creating your app in ~11 lines of code*


```php
	require 'vendor/autoload.php'; // Include composer autoloader

	$mb = \Minibase\MB::create(); // Create one application object.
	
	// Add a route for your homepage
	$mb->route("get", "/", function () {
		// some logic.
		// And return a HtmlResponse object
		return $this->respond("html")->view("views/test.html.php");
	});
	
	// Start the router engine.
	$mb->start();
```

