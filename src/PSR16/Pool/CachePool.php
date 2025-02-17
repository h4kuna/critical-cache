<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\PSR16\Pool;

use DateInterval;
use Generator;
use h4kuna\CriticalCache\PSR16\Expire;
use Psr\Clock\ClockInterface;
use Psr\SimpleCache\CacheInterface;

final class CachePool implements CacheInterface
{
	private const KeyTtl = 'ttl';
	private const KeyData = 'data';

	/**
	 * @param array<CacheInterface> $caches
	 */
	public function __construct(private array $caches, private ClockInterface $clock)
	{
	}

	public function clear(): bool
	{
		$return = false;
		foreach ($this->caches as $cache) {
			$return = $cache->clear() || $return;
		}

		return $return;
	}

	/**
	 * @return Generator<string, mixed>
	 */
	public function getMultiple(iterable $keys, mixed $default = null): iterable
	{
		foreach ($keys as $key) {
			yield $key => $this->get($key, $default);
		}
	}

	public function get(string $key, mixed $default = null): mixed
	{
		$backup = [];
		foreach ($this->caches as $cache) {
			/** @var ?array{data: mixed, ttl: int} $result */
			$result = $cache->get($key, null);
			if ($result !== null) {
				$this->saveToParents($backup, $result, $key);

				return $result[self::KeyData];
			}
			$backup[] = $cache;
		}

		return $result ?? $default;
	}

	/**
	 * @param array<CacheInterface> $backup
	 * @param array{data: mixed, ttl: ?int} $result
	 */
	private function saveToParents(array $backup, array $result, string $key): void
	{
		if ($backup === []) {
			return;
		}

		$ttl = $result[self::KeyTtl] === null ? null : $result[self::KeyTtl] - $this->clock->now()->getTimestamp();
		foreach ($backup as $cache) {
			$cache->set($key, $result, $ttl);
		}
	}

	public function set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool
	{
		if ($value === null) {
			$this->delete($key);

			return false;
		}

		$return = false;
		foreach ($this->caches as $cache) {
			$return = $cache->set($key, [
					self::KeyData => $value,
					self::KeyTtl => Expire::at($ttl, $this->clock),
				], Expire::after($ttl, $this->clock)) || $return;
		}

		return $return;
	}

	public function delete(string $key): bool
	{
		$return = false;
		foreach ($this->caches as $cache) {
			$return = $cache->delete($key) || $return;
		}

		return $return;
	}

	public function setMultiple(iterable $values, DateInterval|int|null $ttl = null): bool
	{
		$return = false;
		foreach ($values as $key => $value) {
			/** @var int|string $key */
			$return = $this->set((string) $key, $value, $ttl) || $return;
		}

		return $return;
	}

	public function deleteMultiple(iterable $keys): bool
	{
		$return = false;
		foreach ($keys as $key) {
			$return = $this->delete($key) || $return;
		}

		return $return;
	}

	public function has(string $key): bool
	{
		foreach ($this->caches as $cache) {
			if ($cache->has($key)) {
				return true;
			}
		}

		return false;
	}
}
