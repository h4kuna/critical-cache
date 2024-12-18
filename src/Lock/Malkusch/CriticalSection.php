<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Lock\Malkusch;

use h4kuna\CriticalCache\Lock;
use Malkusch\Lock\Mutex\Mutex;

final class CriticalSection implements Lock\Lock
{
	public function __construct(private Mutex $lockMutex)
	{
	}

	public function synchronized(callable $callback)
	{
		return $this->lockMutex->synchronized($callback);
	}

}
