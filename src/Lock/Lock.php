<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Lock;

interface Lock
{
	/**
	 * @template T
	 * @param callable(): T $callback
	 *
	 * @return T
	 */
	function synchronized(callable $callback);
}
