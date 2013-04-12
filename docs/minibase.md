# Minibase



## Configuration


### Set view path

By default there is no default view path where your views are stored when using the `HtmlResponse`, you can set a view path like so:

```php
$mb->setConfig(MB::CFG_VIEWPATH, __DIR__ . '/views/');
```

When using the `$this->respond("html")->view("views/home.html.php")` helper, it's no longer needed to define `views` in front of the path. The same goes for using the `$this->import("some_view.php"` helper in the View files.


