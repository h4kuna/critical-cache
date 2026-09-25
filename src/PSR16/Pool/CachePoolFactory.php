<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\PSR16\Pool;

use Beste\Clock\SystemClock;
use h4kuna\CriticalCache\Exceptions\MissingDependencyException;
use h4kuna\CriticalCache\PSR16\MemoryCache;
use h4kuna\CriticalCache\PSR16\PSR16CacheFactory;
use Psr\Clock\ClockInterface;
use Psr\SimpleCache\CacheInterface;
use function is_array;

final class CachePoolFactory implements CachePoolFactoryInterface
{

	private ClockInterface $clock;

	public function __construct(
		private PSR16CacheFactory $cacheFactory,
		?ClockInterface $clock = null,
	)
	{
		$this->clock = $clock ?? self::createClock();
	}

	private static function createClock(): ClockInterface
	{
		MissingDependencyException::checkBesteClock();

		return SystemClock::create();
	}

	public function create(string|array $namespace = ''): CacheInterface
	{
		return new CachePool(
			is_array($namespace)
				? $namespace
				: [new MemoryCache($this->clock), $this->cacheFactory->create($namespace)],
			$this->clock,
		);
	}

}
