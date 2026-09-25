<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\PSR16;

use Closure;
use h4kuna\CriticalCache\Utils\Dependency;
use Psr\SimpleCache\CacheInterface;

interface CacheLocking extends CacheInterface
{

	/**
	 * @param Closure(Dependency, CacheInterface, string): T $callback
	 * @return T
	 *
	 * @template T
	 */
	public function load(
		string $key,
		Closure $callback,
	);

	/**
	 * @param Closure(CacheInterface): T $callback
	 * @return T
	 *
	 * @template T
	 */
	public function synchronized(
		string $key,
		Closure $callback,
	);

}
