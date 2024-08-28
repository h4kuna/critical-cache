<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\PSR16;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\Clock\ClockInterface;

final class Expire
{
	public static function at(int|null|DateInterval|DateTimeInterface $ttl, ?ClockInterface $clock = null): ?int
	{
		if ($ttl === null) {
			return null;
		} elseif (is_int($ttl)) {
			return self::fromInt($ttl, self::createDateTimeImmutable($clock));
		} elseif ($ttl instanceof DateTimeInterface) {
			return $ttl->getTimestamp();
		}
		return self::fromInterval($ttl, self::createDateTimeImmutable($clock));
	}

	public static function fromInt(int $ttl, DateTimeImmutable $now): int
	{
		return $now->getTimestamp() + $ttl;
	}

	private static function createDateTimeImmutable(?ClockInterface $clock = null): DateTimeImmutable
	{
		return $clock === null ? new DateTimeImmutable() : $clock->now();
	}

	public static function fromInterval(DateInterval $dateInterval, DateTimeImmutable $now): int
	{
		return $now->add($dateInterval)->getTimestamp();
	}

	public static function after(int|null|DateInterval|DateTimeInterface $ttl, ?ClockInterface $clock = null): ?int
	{
		if ($ttl === null || is_int($ttl)) {
			return $ttl;
		}

		$now = self::createDateTimeImmutable($clock);
		if ($ttl instanceof DateTimeInterface) {
			$timestamp = $ttl->getTimestamp();
		} else {
			$timestamp = self::fromInterval($ttl, $now);
		}

		return $timestamp - $now->getTimestamp();
	}
}
