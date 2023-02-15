<?php declare(strict_types=1);

namespace h4kuna\CriticalCache;

use Psr\SimpleCache\CacheInterface;

interface CacheLocking extends CacheInterface
{

	/**
	 * @template T
	 * @param \Closure(): T $callback
	 * @return T
	 */
	function load(string $key, \Closure $callback);


	/**
	 * @template T
	 * @param \Closure(): T $callback
	 * @return T
	 */
	function synchronized(string $key, \Closure $callback);

}
