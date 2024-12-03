<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Nette\Storage;

use Nette\Caching\Cache;
use Nette\Caching\Storage;

final class MemoryTtlStorage implements Storage
{
	private const KeyTtl = 'ttl';
	private const KeyData = 'data';
	private const KeyDependencies = 'dependencies';

	/** @var array<string, array{data: mixed, dependencies: array{expire?: float}}> */
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

	/**
	 * @param array<string, mixed> $meta
	 */
	private static function verify(array $meta): bool
	{
		return isset($meta[self::KeyTtl]) === false || ($meta[self::KeyTtl] >= self::micro());
	}

	private static function micro(): float
	{
		return microtime(true);
	}

	public function lock(string $key): void
	{
	}

	public function write(string $key, $data, array $dependencies): void
	{
		$this->data[$key] = [
			self::KeyDependencies => self::validate($dependencies),
			self::KeyData => $data,
		];
	}

	/**
	 * @return array{expire?: float}
	 */
	private static function validate(array $dependencies): array
	{
		$out = [];
		if (isset($dependencies[Cache::Expire]) && is_numeric($dependencies[Cache::Expire])) {
			$out[self::KeyTtl] = self::micro() + $dependencies[Cache::Expire];
			unset($dependencies[Cache::Expire]);
		}

		return $out;
	}

	public function remove(string $key): void
	{
		unset($this->data[$key]);
	}

	public function clean(array $conditions): void
	{
		if (isset($conditions[Cache::All])) {
			$this->data = [];
		}
	}

}
