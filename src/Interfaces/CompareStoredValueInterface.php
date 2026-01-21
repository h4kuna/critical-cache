<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Interfaces;

interface CompareStoredValueInterface
{
	public const CacheValue = '1';

	public function get(string $key): ?string;

	public function isEqual(string $key, string $value = self::CacheValue): bool;
}
