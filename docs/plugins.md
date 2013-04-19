**Table of Contents**  *generated with [DocToc](http://doctoc.herokuapp.com/)*

- [Plugins](#plugins)
	- [Documentation Index](#documentation-index)
	- [Callback plugins](#callback-plugins)
	- [Class plugins](#class-plugins)

# Plugins

Plugins can be added to the application instance (`Minibase\MB`). 

There are two kinds of plugins in Minibase:

- Class plugins
- Callback plugins



## Documentation Index

* [Callback plugins](#callback-plugins)
* [Class plugins](#class-plugins)



## Callback plugins

You can use the `plugin` method to register your own plugins such as a *database connection*, *library* or anything else you might want to use from your router callbacks.

Use the `plugin` method in `Minibase\MB` to register a new plugin. It's easy to forexample create a database connection like so:


```php
$app->plugin("db", function () {
	return new \PDO("mysql:dbname=testdb;host=127.0.0.1", "user", "password");
});
```

You can then use your database connections easily. 


```php
$app->on("get", "/", function () {
	$news = $this->db->fetch("SELECT * FROM news");
	return ...;
});
```

## Class plugins

Class plugins are more advanced than Callback plugins. Class plugins can be started/stopped with their `start` and `stop` method. Class plugins are also by default started when initialized with the `Minibase\Mb::initPlugins` method.

Plugins can extend the core of Minibase by listening to the `events` and injecting behavior when some event accours.

All Class pugins must extend the abstract class `Minibase\Plugin\Plugin` and must implement the `start` method. The start method should run some code based on some event, and mostly never run anything without events. 


Class plugins can be initialized by your application like so, and should be a part of initialize your app:

```php
$mb->initPlugins([
	'Mynamespace\MyPluginClass' => null /*null or  array of config vars for this plugin */,
	'Some\Other\Namespace\LoggerPlugin' => array('logmethod' => 'file', 'dir' => __DIR__ . '/logs'),
])

// Now start with many $mb->route(...) calls 
```

A callback plugin is registered with class plugins, the name will be the full path to the plugin. If we want to temporary stop our fictional Logger we can run `$mb->get("Some\Other\Namespace\LoggerPlugin")->stop();`, this should stop the event handlers of the plugin and the app will not log until it's started again with `$mb->get("Some\Other\Namespace\LoggerPlugin")->start()`.




