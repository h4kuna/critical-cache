# Critical cache

[![Downloads this Month](https://img.shields.io/packagist/dm/h4kuna/critical-cache.svg)](https://packagist.org/packages/h4kuna/critical-cache)
[![Latest stable](https://img.shields.io/packagist/v/h4kuna/critical-cache.svg)](https://packagist.org/packages/h4kuna/critical-cache)

The library extends PSR-16 about locking when write or delete to cache.

### Installation to project
```sh
$ composer require h4kuna/critical-cache
```

### How to use
First time you can use prepared factory [CacheFactory](./src/CacheFactory.php). The factory tell you what dependency missing. The dependency are not mandatory, because everything can be replaced by your implementation.

```php
use h4kuna\CriticalCache;
$cacheFactory = new CriticalCache\CacheFactory('/my/temp');
$cache = $cacheFactory->create();
assert($cache instanceof Psr\SimpleCache\CacheInterface);

$data = $cache->load('foo', fn() => 'done');
echo $data; // done
```

Method `load` try read from cache, if data is not `null` that success else create critical section by lock system (Mutex), try read from cache, because any parallel process could be faster, if is success unlock critical section and return data, else call callback for create cache, save data to cache and unlock and return data.

## Lock
By default, is used [malkusch/lock](//github.com/php-lock/lock), but if you implement [Lock](src/Lock.php) interface you can use different library.

And by default is used [FlockMutex](//github.com/php-lock/lock/blob/master/classes/mutex/FlockMutex.php) this is reason why is need [thephpleague/flysystem](//github.com/thephpleague/flysystem) and [nette/utils](//github.com/nette/utils). If you use different [Lock](//github.com/php-lock/lock/tree/master/classes/mutex) you don't need previous two libraries.

## Cache
By default, is used [nette/caching](//github.com/nette/caching) and it is implemented by [NetteCache](src/PSR16/NetteCache.php), this class implement PSR-16.
