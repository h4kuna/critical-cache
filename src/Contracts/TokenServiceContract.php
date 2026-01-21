<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Contracts;

use DateTimeImmutable;
use h4kuna\CriticalCache\Interfaces\CompareStoredValueInterface;

interface TokenServiceContract extends CompareStoredValueInterface
{
	public function make(
		int $ttl = 900,
		string $value = self::CacheValue,
		?DateTimeImmutable $validFrom = null,
	): string;
}
