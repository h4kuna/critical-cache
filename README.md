# Critical cache

[![Downloads this Month](https://img.shields.io/packagist/dm/h4kuna/critical-cache.svg)](https://packagist.org/packages/h4kuna/critical-cache)
[![Latest stable](https://img.shields.io/packagist/v/h4kuna/critical-cache.svg)](https://packagist.org/packages/h4kuna/critical-cache)

The library extends PSR-16 about locking when write or delete to cache.

### Installation to project
```bash
composer require h4kuna/critical-cache
```
Optional
```bash
composer require h4kuna/dir malkusch/lock nette/caching beste/clock
```

### How to use
First time you can use prepared factory [CacheFactory](./src/CacheFactory.php). The factory tell you what dependency missing. The dependency are not mandatory, because everything can be replaced by your implementation.

```php
use h4kuna\CriticalCache\PSR16\Locking\CacheLockingFactory;
$cacheFactory = new CacheLockingFactory('/my/temp');
$cache = $cacheFactory->create();
assert($cache instanceof Psr\SimpleCache\CacheInterface);

$data = $cache->load('foo', fn() => 'done');
echo $data; // done
```

Method `load` try read from cache, if data is not `null` that success else create critical section by lock system (Mutex), try read from cache, because any parallel process could be faster, if is success unlock critical section and return data, else call callback for create cache, save data to cache and unlock and return data.

## Pool
Support to use multi-level cache implements CacheInterface. By order Memory -> Filesystem.


```php
use h4kuna\CriticalCache\PSR16\Locking\CacheLockingFactory;
use h4kuna\CriticalCache\PSR16\Pool\CachePoolFactory;

$cacheFactory = new CacheLockingFactory('/my/temp');
$cachePoolFactory = new CachePoolFactory($cacheFactory);

$cache = $cachePoolFactory->create(); // by default create MemoryCache and FileSystem. You can choose redis, memcache.
$cache->set('foo', 1); // write to memory and filesystem

$cache1 = $cachePoolFactory->create();

// try to load from Memory (not found), second is Filesystem (found), and save to Memory, return result. 
echo $cache1->get('foo'); // 1 
```


## Lock
By default, is used [malkusch/lock](//github.com/php-lock/lock), but if you implement [Lock](src/Lock/Lock.php) interface you can use different library.

And by default is used [FlockMutex](//github.com/php-lock/lock/blob/master/classes/mutex/FlockMutex.php) this is reason why is need [h4kuna/dir](//github.com/h4kuna/dir). If you use different [Lock](//github.com/php-lock/lock/tree/master/classes/mutex) you don't need previous library.

## Cache
By default, is used [nette/caching](//github.com/nette/caching) with PSR16 adapter.

## Clock PSR-20
internal cache system beste/clock
