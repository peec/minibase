**Table of Contents**  *generated with [DocToc](http://doctoc.herokuapp.com/)*

- [Events](#events)
	- [Built in events](#built-in-events)
		- [Minibase](#minibase)
			- [mb:start (\Minibase\MB $mb)](#mbstart-\minibase\mb-$mb)
			- [mb:respond:before (array &$map)](#mbrespondbefore-array-&$map)
		- [Routing](#routing)
			- [mb:route:before  (Minibase\Http\Request $request)](#mbroutebefore--minibase\http\request-$request)
			- [mb:route:after (Minibase\Http\Request $request, Minibase\Http\Response, $response)](#mbrouteafter-minibase\http\request-$request-minibase\http\response-$response)
			- [mb:exception:RouteNotFoundException (Minibase\Http\Request $request)](#mbexceptionroutenotfoundexception-minibase\http\request-$request)
		- [View](#view)
			- [before:render (Minibase\Mvc\View $view, array &$viewVars)](#beforerender-minibase\mvc\view-$view-array-&$viewvars)
			- [after:render (Minibase\Mvc\View $view, string &$content)](#afterrender-minibase\mvc\view-$view-string-&$content)
			- [mb:render ()](#mbrender-)

# Events

Remember that Minibase has Event driven architecture. This makes  is really easy to extend functionality of minibase, you can hook into exisiting functionality and add your custom logic. Here we descibe events that Minibase triggers on different scenarios.

You can easily listen on events by using the instance of the event aggregator in you `$mb` object.

Procedural way binding:

```php
$mb->events->on("event name", function (some arguments that gets passed...) {
  // Run something when this event is fired.
});
```

OOP way binding:

```php

class MyEvents extends EventCollection {
	
	/**
	 * @Minibase\Annotation\Event("mb:start")
	 */
	public function lolSac () {
		
	}
}

// load the class
$mb->addEventCollection(new MyEvents());

```


# Built in events


## Minibase

#### mb:start (\Minibase\MB $mb)

Triggered once the `start` method is run.

#### mb:respond:before (array &$map)

`mb:respond:before` is triggered when `$mb->respond(TYPE)` is used. It's possible to add other response types by creating a `on` listener for this events. E.g. If you want to create a custom RSS response type, XML response type or any other response objects. Custom Response objects must extend `Minibase\Http\Response`.

Sample of implementation:

```php
$mb->events->on("mb:respond:before", function (&$map) {
	if (!isset($map['rss'])){
		$map['rss'] = function ($data) {
			$rssFeed = new MyNamespace\RssResponse();
			$rssFeed->setData($data);
			return $rssFeed;
		};
	}
});

// Now you can return rss feed response from your controller callbacks.
$mb->route("get", "/rssfeed", function () {
	return $this->respond("rss", array(/* data from database, */))
		->setAuthor('My Name')
		->customMethod('...etc..');
});
```


## Request

#### mb:exception:InvalidJsonRequestException (Minibase\Http\Request $request)

Called once json was invalid when `$this->request->json()` was used. If not overridden Exception is thrown.

Sample of implementation:


```php
$mb->events->on("mb:exception:InvalidJsonRequestException", function ($request) {

	return function () use ($request) {
		return $this->respond("json")
		->data(array('error' => true, 'msg' => "Raw in data must be valid JSON."))
		->with(400);
	};
});
```


## Routing

#### mb:route:before  (Minibase\Http\Request $request)

Triggered right before running a callback when a route matched the URI. 


#### mb:route:after (Minibase\Http\Request $request, Minibase\Http\Response, $response)

Triggered after a callback for a given route has been executed and content has been served.


#### mb:exception:RouteNotFoundException (Minibase\Http\Request $request)

Triggered when no route matched the uri / request method. Listen on this event to create a custom 404 page. Example:

```php
$mb->events->on("mb:exception:RouteNotFoundException", function ($request) {
	return function () use ($request) {
		return $this->respond("html")
			->view("404.html.php", array('request' => $request))
			->with(404);
	};
});
```



## View

#### before:render (Minibase\Mvc\View $view, array &$viewVars)

Triggered right before we render a template. Global view vars (available for all templates, forexample) is easy to add. Remember that $viewVars is here a reference to an array of view vars with $key->$value being available as a variable in the views.

Sample implementation (Assigning a global `$user` variable if the user was logged in.)

```php
$mb->events->on("before:render", function ($view, &$args) {
	$args['user'] = $this->user ?: null;
	
}, $mb);
```

#### before:render:extension (&$extHandlers)

Allows to add new render methods based on the file extension of the view file being rendered. Forexample the twig plugin 
adds "html" and "twig" extension handlers.


Sample of implementation.

```php
$this->mb->events->on("before:render:extension", function (&$extHandlers) use ($plugin) {
			// Be sure to use isset for performance.
			if (!isset($extHandlers['twig'])) {
				$callback = function ($vars, $view, $viewPath) use ($plugin) {
					// Set current view instance.
					$plugin->currentView = $this;
					$vars['twigMBViewVar'] = $this;
					// ignore $viewPath, we add this to Twig_Loader_Filesystem.
					echo $plugin->twig->render($view, $vars);
				};
				$extHandlers['twig']  = $callback;
				$extHandlers['html']  = $callback;
			}
		});
```		

#### after:render (Minibase\Mvc\View $view, string &$content)


Triggered after content has been rendered, but not echoed out yet. Useful to modify output after it has been rendered.


#### mb:render:$file_extension ()

Should return a callback that renders a view.  `$this` will be bound to the `View` instance. The callback takes 3 arguments.  

- `$vars` view vars assigned from the template.. 
- `$view` The view file.
- `$viewPath` Path to where the view `$view` is located. Can be empty if global configuration is not set.



Default implementation just extracts the variables to vars and includes the view.:

```php
function() {
	return function ($vars, $view, $viewPath) {
		extract($vars);
		include ($viewPath ?: "") . $view;
	};
}

```

## Call execution.

#### mb:call:execute:annotation (\stdClass $annotation, \Minibase\Mvc\Controller $controller)

If you want to extend controllers with custom annotations this is the method to listen on. `$annotation` should be checked with `instanceof`. Annotations are executed before the specific call are executed so custom annotations can forexample. If the event returns a `Minibase\Http\Response` object, execution of the current call is stopped and the specific Response is returned instead. The controller is a instance of the specific controller that the annotation was on.

#### mb:call:execute (Minibase\Http\Request $request, array $annotations)

Triggered just before the controller-method is runned. The callback should return a `Response` or callback if you want to ignore the current method route for execution. The response you provided is instead returned and executed. 


Sample from the CSRF plugin

```php
	function (Request $req, $annotations) {
			$this->setToken();
			
			$skip = $req->method === 'get';
			foreach($annotations as $annotation) {
				// Ignore CSRF protection.
				if ($annotation instanceof IgnoreCsrfProtection) {
					$skip = true;
				}
			}
			
			if (!$skip) { 
				// Return a Minibase\Http\Response.
			}
	}
			
```



## Console


#### mb:console (Symfony\Component\Console\Application $application)

Useful when you want custom commands for your app. Plugins can easily listen to this event and create custom commands. Forexample, the `minibase-plugin-doctrine` plugin uses this event to add Doctrine related commands. 

Example:

```php
$mb->events->on("mb:console", function (Application $console) {
	$console->addCommands(array(
		// DBAL Commands
		new \Doctrine\DBAL\Tools\Console\Command\RunSqlCommand(),
		new \Doctrine\DBAL\Tools\Console\Command\ImportCommand()
		// etc ....
	));		
});
```



