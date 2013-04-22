**Table of Contents**  *generated with [DocToc](http://doctoc.herokuapp.com/)*

- [Minibase](#minibase)
	- [Configuration](#configuration)
		- [Set view path](#set-view-path)

# Minibase


## Autoloading

You might want to register the composer autoloader on your app so you don't need to include files everywhere. It's really simple, just use composer for this.

```php
$loader = require __DIR__ . "/vendor/autoload.php";
$loader->add('', __DIR__ );
```


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


## Configuration file

Minibase has plenty of configuration, it can be messy sometimes to couple everything with code. The best way  to go might be to use the `$mb->loadConfigFile('app.json',__DIR__)` method. It takes two arguments. The first being the json file to load, second the Application Path to where your app base is.

This is a sample configuration file from the `sample` application. 


```json
{
	"routeFiles": ["${APP_DIR}/routes.json"],
	
	"vendorDir": "${APP_DIR}/../vendor",
	"autoLoaders": [
		{
			"ns": "app",
			"path": "${APP_DIR}/.."
		}
	],
	"config": {
		"viewPath": "${APP_DIR}/views/"
	},
	"cacheDriver": {
		"name": "Minibase/Cache/Memcached/MemcachedDriver",
		"config": {
			"servers": [
				["localhost", 11211]
			]
		}
	},
	"eventCollections": [
		"app/events/AppEvents"
	],
	"plugins": [
		{
			"name": "Pkj/Minibase/Plugin/TwigPlugin/TwigPlugin",
			"config": {
				
			}
		},
		{
			"name":"Pkj/Minibase/Plugin/Csrf/CsrfPlugin"
		},
		{
			"name": "Pkj/Minibase/Plugin/DoctrinePlugin/DoctrinePlugin",
			"config": {
				"metadata": "annotation",
				"entityDirs": ["${APP_DIR}/models/"],
				"connection": {
					"driver": "pdo_sqlite",
					"path": "${APP_DIR}/cache/db.sqlite"
				}
			}
		}
	]
}
```

Note the configuration file is standard JSON, but some exceptions.

**Variables**

Variables is possible, you have access to variables with the `${VAR_NAME}` syntax.

- **APP_DIR**: The path to where the app is located.


**Namespaces**

Namespaces are using the forwardslash `/` instead of backslash.


#### routeFiles

Array of routeFiles (String).



#### vendorDir

A string containg the path to where the composer vendor dir is located.

#### autoLoaders

Array of Autoloader Configurations (object).

Requires `vendorDir` to be set. This directive allows you to add autoloaders for your own app. Using composer autoloader.








