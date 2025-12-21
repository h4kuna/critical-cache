<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Mock;

use Beste\Clock\SystemClock;
use h4kuna\CriticalCache\Contracts\ValidServiceContract;
use h4kuna\CriticalCache\Nette\Storage\MemoryTtlStorage;
use h4kuna\CriticalCache\Services\ValidService;
use Nette\Bridges\Psr\PsrCacheAdapter;

final class ValidServiceFactory
{
	public static function create(): ValidServiceContract
	{
		return new ValidService(new PsrCacheAdapter(new MemoryTtlStorage()), SystemClock::create());
	}
}
