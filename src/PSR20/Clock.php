<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\PSR20;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final class Clock implements ClockInterface
{
	public function now(): DateTimeImmutable
	{
		return new DateTimeImmutable();
	}
}
