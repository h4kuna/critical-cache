<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Contracts;

use DateInterval;
use DateTimeInterface;
use h4kuna\CriticalCache\Caching\ValidityAware\TimeRangeItem;

interface ValidityAwareCacheContract
{
	public function get(string $key): TimeRangeItem;

	public function delete(string $key): void;

	public function set(
		string $key,
		int|DateInterval|DateTimeInterface $validTo,
		int|DateInterval|DateTimeInterface|null $validFrom = null,
		string $value = '',
	): void;
}
