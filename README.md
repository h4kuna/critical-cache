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
composer require h4kuna/dir symfony/lock nette/caching beste/clock
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

The method `load()` protects against the cache stampede: when many processes miss the same key at once, only one of them builds the value, the others wait for it. It is double-checked locking:

1. Read the cache, return the data if it is not `null`.
2. Try to take the lock without waiting. The process which gets it reads the cache again (a parallel process could have been faster), calls the callback, saves the result, releases the lock and notifies the waiting processes.
3. The other processes do not call the callback, they wait until the first one finishes and read the cache. If the value is still missing (the first process failed or the wait timed out), they build it one by one under the lock, still checking the cache first.

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

The critical section is [CriticalSection](src/Lock/CriticalSection.php), the default implementation [SymfonyCriticalSection](src/Lock/Symfony/SymfonyCriticalSection.php) uses [symfony/lock](https://symfony.com/doc/current/components/lock.html). Without arguments the factory uses `FlockStore` in the temp directory, which is enough for one server.

For more servers (pods), use a shared store, for example Redis, and pass your own critical section.

```php
use h4kuna\CriticalCache\Lock\Notification\Redis\RedisStreamNotifier;
use h4kuna\CriticalCache\Lock\Symfony\SymfonyCriticalSection;
use h4kuna\CriticalCache\PSR16\Locking\CacheLockingFactory;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\RedisStore;

$redis = RedisAdapter::createConnection('redis://redis:6379'); // or new Redis()

$criticalSection = new SymfonyCriticalSection(
    new LockFactory(new RedisStore($redis)),
    new RedisStreamNotifier($redis), // optional
);

$cacheFactory = new CacheLockingFactory('/my/temp', $criticalSection);
// or with your own PSR-16 cache
$cacheFactory = new CacheLockingFactory($myPSR16CacheFactory, $criticalSection);
```

### Waking up the waiting processes

Without a notifier, the waiting processes wait for the lock and take it one by one after it is released, each of them only to find out that the value is ready. `FlockStore` wakes them up by the operating system, `RedisStore` polls about every 100 ms.

[RedisStreamNotifier](src/Lock/Notification/Redis/RedisStreamNotifier.php) (needs `ext-redis`) wakes all of them up at once. The process which built the value adds an entry to a Redis stream, the waiting processes block on `XREAD`. Unlike Pub/Sub, the stream keeps the entry, so the notification cannot be lost between a failed lock attempt and the start of waiting. The notification is only a signal, the value is read from the cache. If it does not come in `$waitTimeout` (5 seconds by default), the process falls back to the lock.

The lock has a TTL for expiring stores like Redis (`$ttl`, 300 seconds by default), so a crashed process does not block the others forever. Set it longer than your slowest callback.

You can implement [Notifier](src/Lock/Notification/Notifier.php) for a different transport.

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
