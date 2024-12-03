<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit\PSR16\Pool;

use h4kuna\CriticalCache\Nette\NetteCacheFactory;
use h4kuna\CriticalCache\PSR16\Pool\CachePool;
use h4kuna\CriticalCache\PSR16\Pool\CachePoolFactory;
use h4kuna\CriticalCache\Tests\ClockTest;
use h4kuna\Dir\Dir;
use h4kuna\Memoize\PSR16\MemoryCache;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

final class CachePoolFactoryTest extends TestCase
{
	public function testBasic(): void
	{
		$cacheFactory = new NetteCacheFactory(new Dir(__DIR__ . '/../../../../temp'));
		$poolFactory = new CachePoolFactory($cacheFactory, new ClockTest());

		$pool = $poolFactory->create();
		Assert::type(CachePool::class, $pool);

		$pool = $poolFactory->create([new MemoryCache(), $cacheFactory->create()]);
		Assert::type(CachePool::class, $pool);
	}
}

(new CachePoolFactoryTest())->run();
