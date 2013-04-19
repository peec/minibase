**Table of Contents**  *generated with [DocToc](http://doctoc.herokuapp.com/)*

- [Routing](#routing)
	- [Routes with callbacks](#routes-with-callbacks)
	- [Routes the OOP way.](#routes-the-oop-way)
	- [Route methods should return responses.](#route-methods-should-return-responses)
	- [Regular expressions in routes](#regular-expressions-in-routes)
	- [Delivering a JSON Response.](#delivering-a-json-response)
	- [Accepting json as raw format to callbacks.](#accepting-json-as-raw-format-to-callbacks)
	- [Custom 404 page.](#custom-404-page)
	- [Callback parameters](#callback-parameters)
	- [Returning a Minibase\Http\Response](#returning-a-minibase\http\response)
	- [Using .htaccess to remove "index.php"](#using-htaccess-to-remove-indexphp)
	- [Reverse routing](#reverse-routing)
		- [Reverse routing callback routing](#reverse-routing-callback-routing)
		- [Reverse routing with OOP routing](#reverse-routing-with-oop-routing)
		- [Checking if a reversed route is active.](#checking-if-a-reversed-route-is-active)

# Routing

Routing is meant to be simple. You can route HTTP requests to callbacks or object methods. Routing is accessible from the Minibase\MD class. After all routes are defined a single call to `$mb->start()` should be provided so minibase can start finding a matching route based on the current HTTP request.



## Routes with callbacks

A simple route with a callback that returns a view.

```php
$app->route("get", "/", function ($params, $that) {
	// Some logic.
	return $this->respond("html")
		->view("views/homepage.html.php");
});
```

## Routes the OOP way.

You can create route files that contains a JSON formatted file that contains your routes. A sample implementation of a `routes.json` file:


```json
[
	["get", "/test", "MyController.test"]
]
```



And then in your php script:

```php
class MyController extends Minibase\Mvc\Controller{
	
	public function test () {
		return $this->respond("html")
		->view('index.html.php');
	}
}

$mb->loadRouteFile(__DIR__ . '/routes.json');

$mb->start(); // Starts the routing finder.

```



## Route methods should return responses.

The callback must return a instance of `Minibase\Http\Response`. There are some built in response objects such as:

- html: HtmlResponse
- redirect: RedirectResponse
- json: JsonResponse

`$this` is bound to the callback so you can use `$this->respond(response_key)->belonging_method()` to return a response.


**Note! Its possible to inject custom response types, see events chapter of the documentation.**

## Regular expressions in routes

You can also use regular expressions in your routes.

```php
$app->route("get", "/news/(\d+)", function ($params, $that) {
	// use $params[0] (the Id of the news) to fetch from db..
	
	return $this->respond("html")
		->view("views/newsitem.html.php");
});
```


## Delivering a JSON Response.


```php
$app->route("get", "/api/news", function ($params, $that) {
	$arrayOrObjectFromDatabase = ...;
	
	return $this->respond("json")
		->data($arrayOrObjectFromDatabase);
});
```

## Accepting json as raw format to callbacks.

You can use the `$data = $this->request->json()` method inside the callback for a route. This gets a JSON request body as a php array. Useful for creating API's against JS frameworks such as Backbone etc. `$this->request->json()` throws `Minibase\Http\InvalidJsonRequestException` if invalid json is posted.

```php
$mb->route("get", "/", function () {
	$requestData = $this->request->json();
	// return something.	
});
```


### Custom 400 page.

Creating a global 400 error, so you don't have to catch forexample `InvalidJsonRequestException` is easy. A sample implementation of this might be the following:

```php
$mb->events->on("mb:error:400", function ($exception) {
	return $this->respond("json")
		->data(array("message" => "Sorry, bad request. Must be JSON formated."));
}, $mb); // Last argument binds $this to $mb inside the closure.
```

## Callback parameters

There are two parameters passed to the callback, `$params` and `$that`.

- `$params`: Array of arguments in the url if you have a regex route. So `/news/(\d+)/(\d+)` will result in example `[1,2]`
- `$that`: A reference to the instance of `Minibase\MB` (your application object).

Note that closures (anonymos functions) have `$this` bound to the application object.

## Returning a Minibase\Http\Response

Every callback should return instance of `Minibase\Http\Response`, there are some built in Result types such as `Minibase\Http\HtmlResponse`, `Minibase\Http\JsonResponse` and `Minibase\Http\RedirectResponse`.

There are a helper method named `respond` in `Minibase\MB` that returns a Response object.


## Using .htaccess to remove "index.php"

Obviously in production and even in development you would want to have `mod_rewrite` enabled to remove `index.php` from the URL so you get cleaner URLS. You must add rewrite rules aswell as setting a special custom var `SetEnv HTTP_MOD_REWRITE On`

Sample of htaccess:

```htaccess
<IfModule mod_rewrite.c>
	# Custom SetEnv, so reverse router in minibase knows that rewrite is enabled.
	SetEnv HTTP_MOD_REWRITE On
	# Rewrite away index.php.
	RewriteEngine On
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^(.*)$ index.php?/$1 [L]
</IfModule>
```

## Reverse routing

Reverse routing is good, you would definately benefit for reverse routes instead of normal hardcoded urls. Reverse routing is simple. Reverse routing requires that you give a custom flag that is unique (the `$reverseKey`).

Both the controller and view has access to a `call` method. This method returns a `Minibase\Mvc\Call` instance. The call method has a method named `reverse`. The `reverse` method returns a new instance of `Minibase\Mvc\ReversedCall`, it has a __toString method that returns the `url` property of the object.



### Reverse routing callback routing

Sample using callback

```php
$mb->route("get","/", function () {

}, "homepage"); // Last flag important
```

You an reverse this route from any view or in a controller callback using the `call` method in the `MB` or `View` instance. 

From a view file:

```php
<?php echo $this->call('homepage')->reverse() ?>
```

From a callback:

```php
$mb->route("get","/someothermethod", function () {
	echo $this->call('homepage')->reverse();
});

```



### Reverse routing with OOP routing


If you are using the OOP way, things are more convenient for reverserouting, because the `Controller.method` is used as the `$reverseKey`.

JSON router file:

```json
[
	["get","/test", "MyController.test"]
]
```

It's then reversable:

```php
$this->call('MyController.test')->reverse();
```


### Checking if a reversed route is active.

Sometimes you need to check if a route is active.

Take this example:

```php
// returns true if /news/43 is active.
$this->call('News.showNewsItem')->reverse(array(43))->isActive();
```





