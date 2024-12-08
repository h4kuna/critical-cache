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

# Services

## [UseOneTimeService](src/Services/UseOneTimeService.php)

The service is usable for token and use one time.

```php
/** @var \h4kuna\CriticalCache\Services\UseOneTimeService $useOneTimeService */
$timeToLive = 900; // seconds
$useOneTimeService->save('foo', 'token', $timeToLive);
// after 900 seconds or one call $useOneTimeService::get() is removed from cache 

$useOneTimeService->get('foo'); // token
$useOneTimeService->get('foo'); // null
```

## [ValidService](src/Services/ValidService.php)

The service tell you if anything is valid, you can choose date range for valid window.

```php
/** @var \h4kuna\CriticalCache\Services\ValidService $validToService */
$validToService->set('foo', new DateTime('tomorrow midnight')); // from is null it is mean now
$validToService->isValid('foo'); // true from 'now' to 'tomorrow midnight'
$validToService->value('foo'); // return empty string if is valid and null if is invalid
$validToService->from('foo'); // null mean unlimited or DateTimeImmutable
$validToService->to('foo'); // null mean does not exist or DateTimeImmutable
$validToService->isValid('foo'); // true the time is in range, false is out of range

$validToService->set('bar', new DateTime('tomorrow midnight'), new DateTime('+5 minutes'), 'lorem'); // the string 'lorem' it will be a valid after 5 minutes
```

## [TokenService](src/Services/TokenService.php)

The service generate token and keep it for defined time.

```php
/** @var \h4kuna\CriticalCache\Services\TokenService $tokenService */
$token = $tokenService->make(); // return string token by default uuid v4

dump($tokenService->compare($token)); // true / false

// if you want compare your self let use get()
$token = $tokenService->make(value: 'lorem');
$value = $tokenService->get($token); // lorem

$tokenService->compare(value: $value); // false because you use get()
```

## [UniqueHashQueueService](src/Services/UniqueHashQueueService.php)

The service generate unique values, witch check mechanism with source for example, with database. Create lock for critical section get one unique values from queue.

For example, we use [RandomGeneratorMock](tests/src/Mock/RandomGeneratorMock.php), the class generate alphabet, A, B, C, D ... Z, AA...

```php
$checkUniqueValue = new class implements \h4kuna\CriticalCache\Interfaces\UniqueValueServiceInterface  {
    
    public function __construct(
        private RandomGeneratorContract $randomGenerator = new \h4kuna\CriticalCache\Tests\Mock\RandomGeneratorMock(),
    ) {
    }
    
    public function check(array $data): iterable {
        // example: $data = ['A', 'B', 'C', 'D', 'E'];
        // SELECT unique_column FROM foo WHERE unique_column IN ('A', 'B', 'C');
        // return matched values, for example B, C
        
        yield 'B';
        yield 'C';
        // or
        return ['B', 'C'];
    }
    
    public function getQueueSize(): int {
        return  20;
    }

	public function getRandomGenerator(): RandomGeneratorContract {
	    return  $this->randomGenerator;
	}

	public function getTries(): ?int {
	    return null;
	}
};

/** @var \h4kuna\CriticalCache\Services\UniqueHashQueueService $uniqueHash */
$value = $uniqueHash->execute($checkUniqueValue); // random unique value, A
$value = $uniqueHash->execute($checkUniqueValue); // random unique value, D
$value = $uniqueHash->execute($checkUniqueValue); // random unique value, E
```

