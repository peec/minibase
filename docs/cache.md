# The Cache

Minibase includes a interface (`Minibase\Cache\ICache`) and a `setCacheDriver` method in the MB instance. Caching is available from `$mb->cache` after `setCacheDriver` is called.


To enable caching use the `setCacheDriver` method on the `$mb` object.

```
$mb->setCacheDriver(new MyCachingDriver(), array(
  // Configuration for the driver
));
```

## ICache interface


```php
interface ICache {
  const KEY_NOT_FOUND = null;
	public function setup (array $config);
	public function get($key);
	public function delete ($key);
	public function set($key, $value, $expireInSeconds = 0);
	
}
```



## Included drivers

We have included some drivers, drivers are very simple to create.


### Memcached Cache driver

Install memcache and the memcached php extension (sudo apt-get install php5-memcached memcache).

**Note, this is MEMCACHED and not MEMCACHE.**

Sample implementation:

```php
$mb->setCacheDriver(new MemcachedDriver(), array(
  'servers' => array(
		['localhost', 11211]
	),
  // Optional callback to configure ...
  'callback' => function () {
    // $this is now a \Memcached object, so you can configure it further in this callback if needed.
  }
));
```


