<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit\PSR16\Pool;

use Beste\Clock\SystemClock;
use h4kuna\CriticalCache\PSR16\Pool\CachePool;
use Nette\Bridges\Psr\PsrCacheAdapter;
use Nette\Caching\Storages\MemoryStorage;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class CachePoolTest extends TestCase
{
	public function testBasic(): void
	{
		$cache1 = new PsrCacheAdapter(new MemoryStorage());
		$cache2 = new PsrCacheAdapter(new MemoryStorage());

		$cachePool = new CachePool([
			$cache1,
			$cache2,
		], SystemClock::create());

		// get & set
		Assert::null($cachePool->get('foo.test'));
		$cachePool->set('foo.test', 'bar');
		$cache1->delete('foo.test');
		Assert::false($cache1->has('foo.test'));
		Assert::true($cachePool->has('foo.test'));
		Assert::same('bar', $cachePool->get('foo.test'));
		Assert::same(['data' => 'bar', 'ttl' => null], $cache1->get('foo.test'));

		// set with null = delete
		$cachePool->set('foo.test', 'bar');
		Assert::true($cachePool->has('foo.test'));
		$cachePool->set('foo.test', null);
		Assert::false($cachePool->has('foo.test'));

		// delete
		$cachePool->set('foo.test', 'bar');
		Assert::true($cachePool->has('foo.test'));
		$cachePool->delete('foo.test');
		Assert::false($cachePool->has('foo.test'));
		Assert::null($cache1->get('foo.test'));
		Assert::null($cache2->get('foo.test'));

		// clear
		$cachePool->set('test.1', 'bar');
		$cachePool->set('test.2', 'foo');
		$cachePool->clear();
		Assert::null($cache1->get('test.1'));
		Assert::null($cache1->get('test.2'));
		Assert::null($cache2->get('test.1'));
		Assert::null($cache2->get('test.2'));

		// multiple
		Assert::same(true, $cachePool->setMultiple(['test.1' => 'bar', 'test.2' => 'foo']));
		$data = iterator_to_array($cachePool->getMultiple(['test.1', 'test.2']));
		Assert::same(['test.1' => 'bar', 'test.2' => 'foo'], $data);
		Assert::true($cachePool->deleteMultiple(['test.1', 'test.2']));
		$data = iterator_to_array($cachePool->getMultiple(['test.1', 'test.2']));
		Assert::same(['test.1' => null, 'test.2' => null], $data);
	}

}

(new CachePoolTest())->run();
