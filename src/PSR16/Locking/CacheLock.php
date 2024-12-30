<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\PSR16\Locking;

use Closure;
use DateInterval;
use h4kuna\CriticalCache\Lock\LockOriginal;
use h4kuna\CriticalCache\PSR16\CacheLocking;
use h4kuna\CriticalCache\Utils\Dependency;
use Psr\SimpleCache\CacheInterface;

final class CacheLock implements CacheLocking
{
	public function __construct(
		private CacheInterface $cache,
		private LockOriginal $lockOriginal,
	) {
	}

	public function clear(): bool
	{
		return $this->synchronized(__METHOD__, static fn (CacheInterface $cache): bool => $cache->clear());
	}

	public function synchronized(string $key, Closure $callback)
	{
		return $this->lockOriginal->get("_lock.$key")->synchronized(fn () => $callback($this->cache));
	}

	/**
	 * Without locking
	 */
	public function get(string $key, mixed $default = null): mixed
	{
		return $this->cache->get($key, $default);
	}

	/**
	 * @param iterable<string> $keys
	 *
	 * @return iterable<string, mixed>
	 */
	public function getMultiple(iterable $keys, mixed $default = null): iterable
	{
		foreach ($keys as $key) {
			yield $key => $this->get($key) ?? $default;
		}
	}

	public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
	{
		$return = false;
		foreach ($values as $key => $value) {
			/** @var int|string $key */
			$return = $this->set((string) $key, $value, $ttl) || $return;
		}

		return $return;
	}

	public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
	{
		return $this->synchronized($key, static fn (CacheInterface $cache): bool => $cache->set($key, $value, $ttl));
	}

	/**
	 * @param iterable<string> $keys
	 */
	public function deleteMultiple(iterable $keys): bool
	{
		$return = false;
		foreach ($keys as $key) {
			$return = $this->delete($key) || $return;
		}

		return $return;
	}

	public function delete(string $key): bool
	{
		return $this->synchronized($key, static fn (CacheInterface $cache): bool => $cache->delete($key));
	}

	public function has(string $key): bool
	{
		return $this->cache->has($key);
	}

	/**
	 * @template T
	 * @param Closure(Dependency, CacheInterface, string): T $callback
	 *
	 * @return T
	 */
	public function load(string $key, Closure $callback)
	{
		$data = $this->cache->get($key);
		if ($data === null) {
			return $this->synchronized($key, static function (CacheInterface $cache) use ($key, $callback): mixed {
				$data = $cache->get($key);
				if ($data === null) {
					$dependency = new Dependency();
					$data = $callback($dependency, $cache, $key);
					$cache->set($key, $data, $dependency->ttl);
				}

				return $data;
			});
		}

		return $data;
	}

}
