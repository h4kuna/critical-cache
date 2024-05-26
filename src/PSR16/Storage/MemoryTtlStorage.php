<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\PSR16\Storage;

use Nette\Caching\Cache;
use Nette\Caching\Storage;

final class MemoryTtlStorage implements Storage
{
	private const KeyTtl = 'ttl';
	private const KeyData = 'data';
	private const KeyDependencies = 'dependencies';

	/** @var array<mixed, array{data: mixed, dependencies: array<string, mixed>}> */
	private array $data = [];


	/**
	 * @return mixed|null
	 */
	public function read(string $key): mixed
	{
		if (isset($this->data[$key]) && self::verify($this->data[$key][self::KeyDependencies])) {
			return $this->data[$key][self::KeyData];
		}
		unset($this->data[$key]);

		return null;
	}


	public function lock(string $key): void
	{
	}


	/**
	 * @param mixed $data
	 * @param array<string, mixed> $dependencies
	 */
	public function write(string $key, $data, array $dependencies): void
	{
		$this->data[$key] = [
			self::KeyDependencies => self::validate($dependencies),
			self::KeyData => $data,
		];
	}


	public function remove(string $key): void
	{
		unset($this->data[$key]);
	}


	/**
	 * @param array<string, mixed> $conditions
	 */
	public function clean(array $conditions): void
	{
		if (isset($conditions[Cache::All])) {
			$this->data = [];
		}
	}


	/**
	 * @param array<string, mixed> $dependencies
	 * @return array<string, mixed>
	 */
	private static function validate(array $dependencies): array
	{
		if (isset($dependencies[Cache::Expire])) {
			$dependencies[self::KeyTtl] = self::micro() + $dependencies[Cache::Expire];
			unset($dependencies[Cache::Expire]);
		}

		return $dependencies;
	}


	/**
	 * @param array<string, mixed> $meta
	 */
	private static function verify(array $meta): bool
	{
		if (isset($meta[self::KeyTtl]) && ($meta[self::KeyTtl] - self::micro()) <= 0) {
			return false;
		}

		return true;
	}


	private static function micro(): float
	{
		return microtime(true);
	}

}
