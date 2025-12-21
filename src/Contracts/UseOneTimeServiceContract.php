<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Contracts;

use DateInterval;
use DateTimeImmutable;

interface UseOneTimeServiceContract
{
	public function save(string $key, string $value, int|DateInterval $ttl = 900, ?DateTimeImmutable $validFrom = null): string;

	public function get(string $key): ?string;
}
