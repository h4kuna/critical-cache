<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Contracts;

interface TokenServiceContract
{
	public const CacheValue = '1';

	public function make(int $ttl = 900, string $value = self::CacheValue): string;

	public function get(string $token): ?string;

	public function compare(string $token, string $value = self::CacheValue): bool;
}
