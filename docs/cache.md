# The Cache

Minibase uses Doctrine's Cache interface. [Many](http://docs.doctrine-project.org/en/2.0.x/reference/caching.html) caching engines such as Apc, Memcache, Memcached etc.


By default `ArrayCache` is used in development, custom cache driver is not set if in development. 



## Included Array configurations

We include a Array configuration interface for some cache engines so that it's easy to enable caching.

### Memcached Cache driver

Install memcache and the memcached php extension (sudo apt-get install php5-memcached memcache).

**Note, this is MEMCACHED and not MEMCACHE.**

Sample implementation:

```php
use Minibase/Cache/ConfigureMemcached;

$mb->configureCacheDriver(new ConfigureMemcached(), array(
  'servers' => array(
		['localhost', 11211]
	),
  // Optional callback to configure ...
  'callback' => function () {
    // $this is now a \Memcached object, so you can configure it further in this callback if needed.
  }
));

// This sets $this->cache to be a instance of Doctrine\Common\Cache\CacheProvider

```


