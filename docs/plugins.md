# Plugins

Plugins can be added to the application instance (`Minibase\MB`). You can use the `plugin` method to register your own plugins such as a *database connection*, *library* or anything else you might want to use from your router callbacks.


Note that the its in a callback so that it's not initialized once you use the `plugin` method, but once you use it with `$app->myplugin`. 


### Registering a database connection.


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


