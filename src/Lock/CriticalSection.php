<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Lock;

use h4kuna\CriticalCache\Lock;
use malkusch\lock\mutex\LockMutex;

final class CriticalSection implements Lock
{
	public function __construct(private LockMutex $lockMutex)
	{
	}


	public function synchronized(callable $code)
	{
		return $this->lockMutex->synchronized($code);
	}

}
