<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Lock\Malkusch;

use h4kuna\CriticalCache\Lock\Lock;
use Malkusch\Lock\Mutex\Mutex;

final class CriticalSection implements Lock
{

	public function __construct(private Mutex $lockMutex)
	{
	}

	/**
	 * @param callable(): T $callback
	 * @return T
	 *
	 * @template T
	 */
	public function synchronized(callable $callback)
	{
		return $this->lockMutex->synchronized($callback);
	}

}
