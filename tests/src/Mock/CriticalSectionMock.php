<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Tests\Mock;

use Closure;
use h4kuna\CriticalCache\Lock\CriticalSection;

final readonly class CriticalSectionMock implements CriticalSection
{

	public function synchronized(
		string $name,
		Closure $callback,
	): mixed
	{
		return $callback();
	}

	public function singleFlight(
		string $name,
		Closure $callback,
		Closure $afterWait,
	): mixed
	{
		return $callback();
	}

}
