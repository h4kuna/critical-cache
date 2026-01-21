<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Services;

use DateInterval;
use DateTimeImmutable;
use h4kuna\CriticalCache\Contracts\UseOneTimeServiceContract;
use h4kuna\CriticalCache\Contracts\ValidityAwareCacheContract;
use h4kuna\CriticalCache\PSR16\Expire;

final readonly class UseOneTimeService implements UseOneTimeServiceContract
{
	public function __construct(
		private ValidityAwareCacheContract $validService,
	) {
	}

	public function set(
		string $key,
		string $value = self::CacheValue,
		int|DateInterval $ttl = 900,
		?DateTimeImmutable $validFrom = null,
	): string {
		$this->validService->set($key, $ttl, $validFrom, $value);

		return $value;
	}

	public function get(string $key): ?string
	{
		$stored = $this->validService->get($key)->value();
		if ($stored === null) {
			return null;
		}

		$this->validService->delete($key);

		return $stored;
	}

	public function isEqual(string $key, string $value = self::CacheValue): bool
	{
		return $this->get($key) === $value;
	}

}
