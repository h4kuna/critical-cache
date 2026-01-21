<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Mock;

use Beste\Clock\SystemClock;
use h4kuna\CriticalCache\Caching\ValidityAware\TimeRangeEncoder;
use h4kuna\CriticalCache\Caching\ValidityAwareCache;
use h4kuna\CriticalCache\Contracts\ValidityAwareCacheContract;
use h4kuna\CriticalCache\Nette\Storage\MemoryTtlStorage;
use Nette\Bridges\Psr\PsrCacheAdapter;

final class ValidServiceFactory
{
	public static function create(): ValidityAwareCacheContract
	{
		$clock = SystemClock::create();
		return new ValidityAwareCache(new PsrCacheAdapter(new MemoryTtlStorage($clock)), $clock, new TimeRangeEncoder($clock));
	}
}
