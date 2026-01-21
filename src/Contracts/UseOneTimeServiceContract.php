<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Contracts;

use DateInterval;
use DateTimeImmutable;
use h4kuna\CriticalCache\Interfaces\CompareStoredValueInterface;

interface UseOneTimeServiceContract extends CompareStoredValueInterface
{
	public function set(
		string $key,
		string $value = self::CacheValue,
		int|DateInterval $ttl = 900,
		?DateTimeImmutable $validFrom = null,
	): string;
}
