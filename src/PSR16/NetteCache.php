<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\PSR16;

use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\NotImplementedException;
use Psr\SimpleCache\CacheInterface;

final class NetteCache implements CacheInterface
{
	public function __construct(private Storage $storage)
	{
	}


	public function get(string $key, mixed $default = null): mixed
	{
		return $this->storage->read($key) ?? $default;
	}


	public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
	{
		$dependencies = [];
		if ($ttl !== null) {
			if ($ttl instanceof \DateInterval) {
				throw new \RuntimeException('Use DateInterval like ttl is not implemented.');
			}
			$dependencies[Cache::Expire] = $ttl;
		}

		$this->storage->write($key, $value, $dependencies);

		return true;
	}


	public function delete(string $key): bool
	{
		$this->storage->remove($key);

		return true;
	}


	public function clear(): bool
	{
		$this->storage->clean([Cache::All => true]);

		return true;
	}


	public function getMultiple(iterable $keys, mixed $default = null): iterable
	{
		foreach ($keys as $key => $name) {
			assert(is_string($key) || is_int($key) || is_float($key));
			yield (string) $key => $this->get($name, $default);
		}
	}


	/**
	 * @param iterable<string|int|float, mixed> $values
	 */
	public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
	{
		foreach ($values as $key => $value) {
			$this->set((string) $key, $value, $ttl);
		}

		return true;
	}


	public function deleteMultiple(iterable $keys): bool
	{
		foreach ($keys as $value) {
			$this->delete($value);
		}

		return true;
	}


	public function has(string $key): bool
	{
		return $this->storage->read($key) !== null;
	}

}
