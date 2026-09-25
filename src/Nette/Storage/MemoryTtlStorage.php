<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Nette\Storage;

use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Psr\Clock\ClockInterface;
use function is_numeric;

final class MemoryTtlStorage implements Storage
{

	private const KEY_TTL = 'ttl';
	private const KEY_DATA = 'data';
	private const KEY_DEPENDENCIES = 'dependencies';

	/**
	 * @var array<string, array{data: mixed, dependencies: array{expire?: float}}>
	 */
	private array $data = [];

	public function __construct(private readonly ClockInterface $clock)
	{
	}

	public function read(string $key): mixed
	{
		if (isset($this->data[$key]) && $this->verify($this->data[$key][self::KEY_DEPENDENCIES])) {
			return $this->data[$key][self::KEY_DATA];
		}
		unset($this->data[$key]);

		return null;
	}

	/**
	 * @param array<string, mixed> $meta
	 */
	private function verify(array $meta): bool
	{
		return isset($meta[self::KEY_TTL]) === false || ($meta[self::KEY_TTL] >= $this->micro());
	}

	private function micro(): float
	{
		return (float) $this->clock->now()->format('U.u');
	}

	public function lock(string $key): void
	{
	}

	/**
	 * @param mixed $data
	 */
	public function write(
		string $key,
		$data,
		array $dependencies,
	): void
	{
		$this->data[$key] = [
			self::KEY_DEPENDENCIES => self::validate($dependencies),
			self::KEY_DATA => $data,
		];
	}

	/**
	 * @return array{expire?: float}
	 */
	private function validate(array $dependencies): array
	{
		$out = [];
		if (isset($dependencies[Cache::Expire]) && is_numeric($dependencies[Cache::Expire])) {
			$out[self::KEY_TTL] = $this->micro() + $dependencies[Cache::Expire];
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
