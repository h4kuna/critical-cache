<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\PSR16;

use DateInterval;
use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final class Expire
{
	public static function at(int|null|DateInterval $ttl, ClockInterface $clock): ?int
	{
		if ($ttl === null) {
			return null;
		} elseif (is_int($ttl)) {
			return self::fromInt($ttl, $clock->now());
		}
		return self::fromInterval($ttl, $clock->now());
	}

	public static function after(int|null|DateInterval $ttl, ClockInterface $clock): ?int
	{
		if ($ttl === null || is_int($ttl)) {
			return $ttl;
		}

		$now = $clock->now();
		return self::fromInterval($ttl, $now) - $now->getTimestamp();
	}

	public static function fromInt(int $ttl, DateTimeImmutable $now): int
	{
		return $now->getTimestamp() + $ttl;
	}

	public static function fromInterval(DateInterval $dateInterval, DateTimeImmutable $now): int
	{
		return $now->add($dateInterval)->getTimestamp();
	}
}
