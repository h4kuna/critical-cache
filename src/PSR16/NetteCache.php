<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\PSR16;

use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Psr\SimpleCache\CacheInterface;

final class NetteCache implements CacheInterface
{
	public function __construct(private Storage $storage)
	{
	}


	public function get($key, mixed $default = null): mixed
	{
		return $this->storage->read($key) ?? $default;
	}


	public function set($key, $value, $ttl = null): bool
	{
		$dependencies = [];
		if ($ttl !== null) {
			$dependencies[Cache::Expire] = $ttl;
		}

		$this->storage->write($key, $value, $dependencies);

		return true;
	}


	public function delete($key): bool
	{
		$this->storage->remove($key);

		return true;
	}


	public function clear(): bool
	{
		$this->storage->clean([Cache::All => true]);

		return true;
	}


	/**
	 * @param iterable<string|int, string> $keys
	 * @return iterable<string|int, mixed>
	 */
	public function getMultiple($keys, mixed $default = null): iterable
	{
		foreach ($keys as $key => $name) {
			yield $key => $this->get($name, $default);
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
		foreach ($keys as $value) {
			$this->delete($value);
		}

		return true;
	}


	public function has($key): bool
	{
		return $this->storage->read($key) !== null;
	}

}
