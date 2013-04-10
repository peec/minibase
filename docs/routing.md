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

