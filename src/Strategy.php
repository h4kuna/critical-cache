<?php declare(strict_types=1);

namespace h4kuna\CriticalCache;

use Closure;
use DateInterval;
use h4kuna\CriticalCache\Exceptions\InvalidStateException;
use h4kuna\CriticalCache\Utils\Dependency;
use Psr\SimpleCache\CacheInterface;

final class Strategy implements CacheInterface
{
	public const NoBreak = null;
	private const Delimiter = '.';

	private ?string $breakPoint = self::NoBreak;

	private string $namespace = '';


	/**
	 * @param array<string, CacheInterface> $caches
	 */
	public function __construct(private array $caches)
	{
	}


	public function setStrategy(?string $breakPoint, string $namespace = ''): self
	{
		if ($breakPoint === null && $namespace === '') {
			throw new InvalidStateException('BreakPoint and namespace are empty. Not allowed.');
		} elseif ($breakPoint !== null && isset($this->caches[$breakPoint]) === false) {
			throw new InvalidStateException(sprintf('BreakPoint is missing in array of caches %s.', $breakPoint));
		}

		$strategy = new self($this->caches);
		$strategy->breakPoint = $breakPoint;
		if ($this->namespace === '' && $namespace === '') {
			$ns = '';
		} else {
			$ns = $this->namespace . $namespace . self::Delimiter;
		}
		$strategy->namespace = $ns;

		return $strategy;
	}


	public function get(string $key, mixed $default = null): mixed
	{
		foreach ($this->caches as $name => $cache) {
			$result = $cache->get($this->namespace . $key);
			if ($result !== null) {
				return $result;
			} elseif ($this->breakPoint === $name) {
				break;
			}
		}

		return $this->useDefault($key, $default);
	}


	public function set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool
	{
		$return = false;
		foreach ($this->caches as $name => $cache) {
			$return = $cache->set($this->namespace . $key, $value, $ttl) || $return;
			if ($this->breakPoint === $name) {
				break;
			}
		}

		return $return;
	}


	public function delete(string $key): bool
	{
		$return = false;
		foreach ($this->caches as $cache) {
			$return = $cache->delete($this->namespace . $key) || $return;
		}

		return $return;
	}


	public function clear(): bool
	{
		$return = false;
		foreach ($this->caches as $cache) {
			$return = $cache->clear() || $return;
		}

		return $return;
	}


	private function useDefault(string $key, mixed $default): mixed
	{
		if (($default instanceof Closure) === false) {
			return $default;
		}
		$dependency = new Dependency;
		$value = $default($dependency);
		$this->set($key, $value, $dependency->ttl);

		return $value;
	}


	public function getMultiple(iterable $keys, mixed $default = null): iterable
	{
		throw new InvalidStateException('Not implemented');
	}


	public function setMultiple(iterable $values, DateInterval|int|null $ttl = null): bool
	{
		throw new InvalidStateException('Not implemented');
	}


	public function deleteMultiple(iterable $keys): bool
	{
		throw new InvalidStateException('Not implemented');
	}


	public function has(string $key): bool
	{
		throw new InvalidStateException('Not implemented');
	}

}
