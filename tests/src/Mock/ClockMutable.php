<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Mock;

use DateTime;
use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final readonly class ClockMutable implements ClockInterface
{
	public function __construct(public DateTime $dataTime = new DateTime())
	{
	}

	public function now(): DateTimeImmutable
	{
		return DateTimeImmutable::createFromMutable($this->dataTime);
	}

}
