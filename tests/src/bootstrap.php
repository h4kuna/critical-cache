<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests;

use DateTimeImmutable;
use Tester;

require __DIR__ . '/../../vendor/autoload.php';

Tester\Environment::setup();

final class ClockTest implements \Psr\Clock\ClockInterface
{
	public const Time = 1577934245; // 2020-01-02 03:04:05

	public function now(): DateTimeImmutable
	{
		return (new DateTimeImmutable())->setTimestamp(self::Time);
	}
}
