<?php declare(strict_types=1);

namespace h4kuna\CriticalCache;

use Psr\SimpleCache\CacheInterface;

class Cache implements CacheLocking
{
	private string $namespace = '';

	private CacheInterface $cache;


	public function __construct(
		private PSR16CacheFactory $cacheFactory,
		private LockOriginal $lockOriginal,
	)
	{
		$this->createCache();
	}


	public function namespace(string $namespace): self
	{
		$clone = clone $this;
		$clone->namespace = ltrim("$clone->namespace/$namespace", '/');
		$clone->createCache();

		return $clone;
	}


	/**
	 * Without locking
	 */
	public function get($key, mixed $default = null): mixed
	{
		return $this->cache->get($this->key($key));
	}


	public function set($key, $value, $ttl = null): bool
	{
		return $this->synchronized($key, fn (): bool => $this->cache->set($this->key($key), $value, $ttl));
	}


	public function delete($key): bool
	{
		return $this->synchronized($key, fn (): bool => $this->cache->delete($this->key($key)));
	}


	public function clear(): bool
	{
		return $this->synchronized(__METHOD__, fn (): bool => $this->cache->clear());
	}


	/**
	 * @param iterable<string|int, string> $keys
	 * @return iterable<string|int, mixed>
	 */
	public function getMultiple($keys, mixed $default = null): iterable
	{
		foreach ($keys as $key) {
			yield $key => $this->get($key) ?? $default;
		}
	}


	/**
	 * @param iterable<string, mixed> $values
	 */
	public function setMultiple($values, $ttl = null): bool
	{
		foreach ($values as $key => $value) {
			$this->set($key, $value, $ttl);
		}

		return true;
	}


	/**
	 * @param iterable<string> $keys
	 */
	public function deleteMultiple($keys): bool
	{
		foreach ($keys as $key) {
			$this->delete($key);
		}

		return true;
	}


	public function has($key): bool
	{
		return $this->synchronized($key, fn (): bool => $this->cache->has($this->key($key)));
	}


	/**
	 * @template T
	 * @param \Closure(): T $callback
	 * @return T
	 */
	public function load(string $key, \Closure $callback, \DateInterval|int|null $ttl = null)
	{
		$cacheKey = $this->key($key);

		$data = $this->cache->get($cacheKey);
		if ($data === null) {
			return $this->synchronized($key, function () use ($cacheKey, $callback, $ttl): mixed {
				$data = $this->cache->get($cacheKey);
				if ($data === null) {
					$data = $callback();
					$this->cache->set($cacheKey, $data, $ttl);
				}

				return $data;
			});
		}

		return $data;
	}


	/**
	 * @template T
	 * @param \Closure(): T $callback
	 * @return T
	 */
	public function synchronized(string $key, \Closure $callback)
	{
		return $this->lockOriginal->get($this->key($key))->synchronized($callback);
	}


	private function key(string $key): string
	{
		return $this->namespace . ".$key";
	}


	private function createCache(): void
	{
		$this->cache = $this->cacheFactory->create($this->namespace);
	}

}
