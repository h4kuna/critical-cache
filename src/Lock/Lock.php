<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Lock;

interface Lock
{

	/**
	 * @param callable(): T $callback
	 * @return T
	 *
	 * @template T
	 */
	public function synchronized(callable $callback);

}
