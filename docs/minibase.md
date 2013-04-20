**Table of Contents**  *generated with [DocToc](http://doctoc.herokuapp.com/)*

- [Minibase](#minibase)
	- [Configuration](#configuration)
		- [Set view path](#set-view-path)

# Minibase



## Configuration


### Set view path

By default there is no default view path where your views are stored when using the `HtmlResponse`, you can set a view path like so:

```php
$mb->setConfig(MB::CFG_VIEWPATH, __DIR__ . '/views/');
```

When using the `$this->respond("html")->view("views/home.html.php")` helper, it's no longer needed to define `views` in front of the path. The same goes for using the `$this->import("some_view.php"` helper in the View files.


## Htaccess

### Development vs Production

When you develop your apps you can tell Minibase that you are currently in development with the `SetEnv` command in a `.htaccess` file. By default APPLICATION_ENV is set to 'production' and `$this->isProduction()`.


```htaccess
SetEnv APPLICATION_ENV development
```

`$mb->isDevelopment()` will now return true.




