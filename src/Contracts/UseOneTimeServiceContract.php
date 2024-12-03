<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Contracts;

use DateInterval;

interface UseOneTimeServiceContract
{
	public function save(string $key, string $value, null|int|DateInterval $ttl = 900): string;

	public function get(string $key): ?string;
}
