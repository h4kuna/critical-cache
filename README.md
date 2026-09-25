# Critical cache

[![Downloads this Month](https://img.shields.io/packagist/dm/h4kuna/critical-cache.svg)](https://packagist.org/packages/h4kuna/critical-cache)
[![Latest stable](https://img.shields.io/packagist/v/h4kuna/critical-cache.svg)](https://packagist.org/packages/h4kuna/critical-cache)

Part of the [h4kuna PHP libraries](https://github.com/h4kuna/library), see the overview of all packages.

The library extends PSR-16 with locking, so only one process at a time can write to or delete from the cache.

### Installation to project

Requires PHP 8.2 or newer.

```bash
composer require h4kuna/critical-cache
```

Optional, needed by the default implementations:

```bash
composer require h4kuna/dir malkusch/lock nette/caching beste/clock
```

### How to use

The easiest way to start is the prepared factory [CacheLockingFactory](src/PSR16/Locking/CacheLockingFactory.php). If an optional dependency is missing, the factory throws an exception that tells you which package to install. The dependencies are not mandatory, because everything can be replaced by your own implementation.

```php
use h4kuna\CriticalCache\PSR16\Locking\CacheLockingFactory;

$cacheFactory = new CacheLockingFactory('/my/temp');
$cache = $cacheFactory->create();
assert($cache instanceof Psr\SimpleCache\CacheInterface);

$data = $cache->load('foo', fn () => 'done');
echo $data; // done
```

The method `load()` first tries to read from the cache. If the data is not `null`, it is returned. Otherwise, a critical section is created by the lock system (mutex) and the cache is read again, because a parallel process could have been faster. If the data is found now, the lock is released and the data is returned. If not, the callback is called, its result is saved to the cache, the lock is released and the data is returned.

The callback receives `h4kuna\CriticalCache\Utils\Dependency`, the inner cache and the key. Set `$dependency->ttl` if the value should expire.

```php
use h4kuna\CriticalCache\Utils\Dependency;

$data = $cache->load('foo', function (Dependency $dependency): string {
    $dependency->ttl = 3600; // seconds
    return 'done';
});
```

Methods `set()`, `delete()` and `clear()` are locked as well. Use `synchronized()` if you need your own critical section.

## Pool

Multi-level cache implementing `CacheInterface`. Caches are used in order Memory -> Filesystem.

```php
use h4kuna\CriticalCache\Nette\NetteCacheFactory;
use h4kuna\CriticalCache\PSR16\Pool\CachePoolFactory;
use h4kuna\Dir\TempDir;

$cacheFactory = new NetteCacheFactory((new TempDir('/my/temp'))->dir('h4kuna/cache')); // dir() creates the directory, nette/caching needs an existing one
$cachePoolFactory = new CachePoolFactory($cacheFactory);

$cache = $cachePoolFactory->create(); // by default MemoryCache and filesystem cache
$cache->set('foo', 1); // write to memory and filesystem

$cache1 = $cachePoolFactory->create();

// try to load from memory (not found), then from filesystem (found), save it to memory and return the result
echo $cache1->get('foo'); // 1
```

You can pass your own list of caches (for example Redis or Memcached) to `create()`.

```php
use h4kuna\CriticalCache\PSR16\MemoryCache;

$cache = $cachePoolFactory->create([new MemoryCache(), $redisCache]);
```

## Lock

By default, [malkusch/lock](https://github.com/php-lock/lock) is used. If you implement the [LockOriginal](src/Lock/LockOriginal.php) and [Lock](src/Lock/Lock.php) interfaces, you can use a different library.

```php
use h4kuna\CriticalCache\PSR16\Locking\CacheLockingFactory;

$cacheFactory = new CacheLockingFactory($myPSR16CacheFactory, $myLockOriginal);
```

The default mutex is [FlockMutex](https://github.com/php-lock/lock/blob/master/src/Mutex/FlockMutex.php), which is why [h4kuna/dir](https://github.com/h4kuna/dir) is needed. If you use a different [mutex](https://github.com/php-lock/lock/tree/master/src/Mutex), you don't need it.

## Cache

By default, [nette/caching](https://github.com/nette/caching) is used with its PSR-16 adapter.

## Clock PSR-20

`CachePoolFactory` uses [beste/clock](https://github.com/beste/clock) if you don't pass your own `Psr\Clock\ClockInterface`.

# Services

## [UseOneTimeService](src/Services/UseOneTimeService.php)

The service stores a value, for example a token, which can be read only once.

```php
/** @var \h4kuna\CriticalCache\Services\UseOneTimeService $useOneTimeService */
$timeToLive = 900; // seconds
$useOneTimeService->set('foo', 'token', $timeToLive);
// the value is removed from the cache after 900 seconds or after the first call of get()

$useOneTimeService->get('foo'); // token
$useOneTimeService->get('foo'); // null
```

## [ValidityAwareCache](src/Caching/ValidityAwareCache.php)

The service tells you whether something is valid. You can choose the time window in which it is valid.

```php
/** @var \h4kuna\CriticalCache\Caching\ValidityAwareCache $validityAwareCache */
$validityAwareCache->set('foo', new DateTime('tomorrow midnight')); // valid from now to tomorrow midnight

$item = $validityAwareCache->get('foo'); // TimeRangeItem
$item->isValid(); // true if now is in the range, false if it is out of the range
$item->value(); // stored value (empty string by default) if valid, null if invalid
$item->from; // null means unlimited, otherwise DateTimeImmutable
$item->to; // null means the key does not exist, otherwise DateTimeImmutable

$validityAwareCache->set('bar', 3600, 300, 'lorem'); // 'lorem' is valid in 5 minutes, for one hour
$validityAwareCache->delete('bar');
```

## [TokenService](src/Services/TokenService.php)

The service generates a token and keeps it for a defined time. The token can be used only once.

```php
/** @var \h4kuna\CriticalCache\Services\TokenService $tokenService */
$token = $tokenService->make(); // string token, uuid v4 by default

$tokenService->isEqual($token); // true, then the token is removed

// if you want to compare the value yourself, use get()
$token = $tokenService->make(value: 'lorem');
$value = $tokenService->get($token); // lorem

$tokenService->isEqual($token, $value); // false, because get() already removed the token
```

## [UniqueHashQueueService](src/Services/UniqueHashQueueService.php)

The service generates unique values, which are checked against a source, for example a database. It creates a lock for the critical section and takes one unique value from the queue.

For the example, we use [RandomGeneratorMock](tests/src/Mock/RandomGeneratorMock.php) from the tests, which generates the alphabet: A, B, C, D ... Z, AA ... In production use [RandomGenerator](src/Services/RandomGenerator.php) (uuid v4) or your own implementation of [RandomGeneratorInterface](src/Interfaces/RandomGeneratorInterface.php).

```php
// implement UniqueValueServiceInterface or extend UniqueValueServiceAbstract
$checkUniqueValue = new class extends \h4kuna\CriticalCache\Services\UniqueValueServiceAbstract {

    public function __construct()
    {
        parent::__construct(new \h4kuna\CriticalCache\Tests\Mock\RandomGeneratorMock(), queueSize: 5);
    }

    public function check(array $data, ?object $dataSet = null): iterable
    {
        // example: $data = ['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E'];
        // SELECT unique_column FROM foo WHERE unique_column IN ('A', 'B', 'C', 'D', 'E');
        // return the values which already exist, for example B, C
        return ['B', 'C'];
    }

};

/** @var \h4kuna\CriticalCache\Services\UniqueHashQueueService $uniqueHash */
$value = $uniqueHash->execute($checkUniqueValue); // unique value, E
$value = $uniqueHash->execute($checkUniqueValue); // unique value, D
$value = $uniqueHash->execute($checkUniqueValue); // unique value, A
```

## [PauseAfterUse](src/Services/PauseAfterUse.php)

The service runs a task and then waits a few seconds before it allows the next run.

```php
use h4kuna\CriticalCache\Services\PauseService;

/** @var \h4kuna\CriticalCache\Services\PauseAfterUse $pauseAfterUse */
/** @var \Psr\Clock\ClockInterface $clock */
$pauseService = new class ($clock, 3) extends PauseService {
    protected function run(): void
    {
        var_dump('hello');
    }
};

$pauseAfterUse->execute($pauseService); // execute run()
$pauseAfterUse->execute($pauseService); // sleep 3 seconds and execute run()
```
