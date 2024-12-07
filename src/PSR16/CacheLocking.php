<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\PSR16;

use Closure;
use h4kuna\CriticalCache\Utils\Dependency;
use Psr\SimpleCache\CacheInterface;

interface CacheLocking extends CacheInterface
{

	/**
	 * @template T
	 * @param Closure(Dependency, CacheInterface, string): T $callback
	 *
	 * @return T
	 */
	function load(string $key, Closure $callback);

	/**
	 * @template T
	 * @param Closure(CacheInterface): T $callback
	 *
	 * @return T
	 */
	function synchronized(string $key, Closure $callback);

}
