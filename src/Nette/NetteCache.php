<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Nette;

use DateInterval;
use DateTimeImmutable;
use Generator;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Psr\SimpleCache\CacheInterface;

/**
 * @deprecated use see
 * @see \Nette\Bridges\Psr\PsrCacheAdapter
 */
final class NetteCache implements CacheInterface
{
	public function __construct(
		private Storage $storage,
	) {
	}

	public function clear(): bool
	{
		$this->storage->clean([Cache::All => true]);

		return true;
	}

	/**
	 * @return Generator<string, mixed>
	 */
	public function getMultiple(iterable $keys, mixed $default = null): Generator
	{
		foreach ($keys as $name) {
			yield $name => $this->get($name, $default);
		}
	}

	public function get(string $key, mixed $default = null): mixed
	{
		return $this->storage->read($key) ?? $default;
	}

	public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
	{
		$ttl = self::ttlToSeconds($ttl);

		foreach ($values as $key => $value) {
			/** @var int|string $key */
			$this->set((string) $key, $value, $ttl);
		}

		return true;
	}

	private static function ttlToSeconds(null|int|DateInterval $ttl = null): ?int
	{
		if ($ttl instanceof DateInterval) {
			return self::dateIntervalToSeconds($ttl);
		}

		return $ttl;
	}

	private static function dateIntervalToSeconds(DateInterval $dateInterval): int
	{
		$now = new DateTimeImmutable;
		$expiresAt = $now->add($dateInterval);
		return $expiresAt->getTimestamp() - $now->getTimestamp();
	}

	public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
	{
		$dependencies = [];
		if ($ttl !== null) {
			$dependencies[Cache::Expire] = self::ttlToSeconds($ttl);
		}

		$this->storage->write($key, $value, $dependencies);

		return true;
	}

	public function deleteMultiple(iterable $keys): bool
	{
		foreach ($keys as $value) {
			$this->delete($value);
		}

		return true;
	}

	public function delete(string $key): bool
	{
		$this->storage->remove($key);

		return true;
	}

	public function has(string $key): bool
	{
		return $this->storage->read($key) !== null;
	}
}
