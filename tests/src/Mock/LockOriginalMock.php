<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Mock;

use h4kuna\CriticalCache\Lock\Lock;
use h4kuna\CriticalCache\Lock\LockOriginal;

final readonly class LockOriginalMock implements LockOriginal
{
	public function get(string $name): Lock
	{
		return new class implements Lock {
			public function synchronized(callable $callback): mixed
			{
				return $callback();
			}
		};
	}

}
