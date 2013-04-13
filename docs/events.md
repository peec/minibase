# Events

Remember that Minibase has Event driven architecture. This makes  is really easy to extend functionality of minibase, you can hook into exisiting functionality and add your custom logic. Here we descibe events that Minibase triggers on different scenarios.

You can easily listen on events by using the instance of the event aggregator in you `$mb` object.

```php
$mb->events->on("event name", function (some arguments that gets passed...) {
  // Run something when this event is fired.
});
```



## Built in events


### Minibase

#### mb:start (\Minibase\MB $mb)

Triggered once the `start` method is run.


### Routing

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



### View

#### before:render (Minibase\Mvc\View $view, array &$viewVars)

Triggered right before we render a template. Global view vars (available for all templates, forexample) is easy to add. Remember that $viewVars is here a reference to an array of view vars with $key->$value being available as a variable in the views.


#### after:render (Minibase\Mvc\View $view, string &$content)


Triggered after content has been rendered, but not echoed out yet. Useful to modify output after it has been rendered.


