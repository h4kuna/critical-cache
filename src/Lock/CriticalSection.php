<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Lock;

use Closure;

interface CriticalSection
{

	/**
	 * Run the callback while holding an exclusive lock, the other processes wait for their turn.
	 *
	 * @param Closure(): T $callback
	 * @return T
	 *
	 * @template T
	 */
	public function synchronized(
		string $name,
		Closure $callback,
	): mixed;

	/**
	 * Only the process which takes the lock runs the callback. The processes coming meanwhile do not run it, they wait
	 * until it finishes and then run $afterWait without the lock. The wait may end early (timeout, crashed process),
	 * so $afterWait must handle a missing result.
	 *
	 * @param Closure(): T $callback
	 * @param Closure(): T $afterWait
	 * @return T
	 *
	 * @template T
	 */
	public function singleFlight(
		string $name,
		Closure $callback,
		Closure $afterWait,
	): mixed;

}
