<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Services;

use DateInterval;
use h4kuna\CriticalCache\Contracts\UseOneTimeServiceContract;
use h4kuna\CriticalCache\Exceptions\BrokenCacheException;
use Psr\SimpleCache\CacheInterface;

final readonly class UseOneTimeService implements UseOneTimeServiceContract
{
	public function __construct(
		private CacheInterface $cache,
	) {
	}

	public function save(string $key, string $value, null|int|DateInterval $ttl = 900): string
	{
		$this->cache->set($key, $value, $ttl);

		return $value;
	}

	public function get(string $key): ?string
	{
		$stored = $this->cache->get($key);
		if ($stored === null) {
			return null;
		}

		$this->cache->delete($key);

		if (is_string($stored) === false) {
			throw new BrokenCacheException('Stored value is not string.');
		}

		return $stored;
	}
}
