<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests;

use DateTimeImmutable;
use Tester;

require __DIR__ . '/../../vendor/autoload.php';

Tester\Environment::setup();

final class ClockTest implements \Psr\Clock\ClockInterface
{
	public const Time = 1577934245; // 2020-01-02 03:04:05

	/**
	 * @param DateTimeImmutable|int<0, max> $now
	 */
	public function __construct(private readonly DateTimeImmutable|int $now = self::Time)
	{
	}

	public function now(): DateTimeImmutable
	{
		if ($this->now instanceof DateTimeImmutable) {
			return $this->now;
		}

		$now = new DateTimeImmutable();
		if ($this->now > 0) {
			return $now->setTimestamp($this->now);
		}

		return $now;
	}
}
