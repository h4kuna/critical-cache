<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\PSR16;

use DateInterval;
use DateTimeImmutable;
use Generator;
use Psr\Clock\ClockInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Simple in-memory PSR-16 cache, data live only in the current process.
 */
final class MemoryCache implements CacheInterface
{

	private const KEY_VALUE = 0;
	private const KEY_EXPIRE = 1;

	/**
	 * @var array<string, array{0: mixed, 1: ?int}>
	 */
	private array $data = [];

	public function __construct(private readonly ?ClockInterface $clock = null)
	{
	}

	public function get(
		string $key,
		mixed $default = null,
	): mixed
	{
		if ($this->has($key) === false) {
			return $default;
		}

		return $this->data[$key][self::KEY_VALUE] ?? $default;
	}

	public function has(string $key): bool
	{
		if (isset($this->data[$key]) === false) {
			return false;
		}

		$expire = $this->data[$key][self::KEY_EXPIRE];
		if ($expire === null || $expire >= $this->now()) {
			return true;
		}

		unset($this->data[$key]);

		return false;
	}

	public function set(
		string $key,
		mixed $value,
		DateInterval|int|null $ttl = null,
	): bool
	{
		$this->data[$key] = [self::KEY_VALUE => $value, self::KEY_EXPIRE => Expire::at($ttl, $this->clock)];

		return true;
	}

	public function delete(string $key): bool
	{
		unset($this->data[$key]);

		return true;
	}

	public function clear(): bool
	{
		$this->data = [];

		return true;
	}

	/**
	 * @param iterable<string> $keys
	 * @return Generator<string, mixed>
	 */
	public function getMultiple(
		iterable $keys,
		mixed $default = null,
	): Generator
	{
		foreach ($keys as $key) {
			yield $key => $this->get($key, $default);
		}
	}

	/**
	 * @param iterable<mixed, mixed> $values
	 */
	public function setMultiple(
		iterable $values,
		DateInterval|int|null $ttl = null,
	): bool
	{
		foreach ($values as $key => $value) {
			/** @var int|string $key */
			$this->set((string) $key, $value, $ttl);
		}

		return true;
	}

	/**
	 * @param iterable<string> $keys
	 */
	public function deleteMultiple(iterable $keys): bool
	{
		foreach ($keys as $key) {
			$this->delete($key);
		}

		return true;
	}

	private function now(): int
	{
		return ($this->clock?->now() ?? new DateTimeImmutable())->getTimestamp();
	}

}
