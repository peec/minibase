# Routing

Routing is ment to be simple. You can route HTTP requests to callbacks or object methods. Routing is accessible from the Minibase\MD class.

The callback must return a instance of `Minibase\Http\Response`. There are some built in response objects such as:

- html: HtmlResponse
- redirect: RedirectResponse
- json: JsonResponse

`$this` is bound to the callback so you can use `$this->respond(response_key)->belonging_method()` to return a response.




A simple route with a callback that returns a view.

```php
$app->on("get", "/", function ($params, $that) {
	// Some logic.
	return $this->respond("html")
		->view("views/homepage.html.php");
});
```

You can also use regular expressions in your routes.

```php
$app->on("get", "/news/(\d+)", function ($params, $that) {
	// use $params[0] (the Id of the news) to fetch from db..
	
	return $this->respond("html")
		->view("views/newsitem.html.php");
});
```


Delivering a JSON Response.


```php
$app->on("get", "/api/news", function ($params, $that) {
	$arrayOrObjectFromDatabase = ...;
	
	return $this->respond("json")
		->data($arrayOrObjectFromDatabase);
});


## Callback parameters

There are two parameters passed to the callback, `$params` and `$that`.

- `$params`: Array of arguments in the url if you have a regex route. So `/news/(\d+)/(\d+)` will result in example `[1,2]`
- `$that`: A reference to the instance of `Minibase\MB` (your application object).

Note that closures (anonymos functions) have `$this` bound to the application object.

## Returning a Minibase\Http\Result

Every callback should return instance of `Minibase\Http\Result`, there are some built in Result types such as `Minibase\Http\HtmlResponse`, `Minibase\Http\JsonResponse` and `Minibase\Http\RedirectResponse`.

There are a helper method named `respond` in `Minibase\MB` that returns a Response object.

