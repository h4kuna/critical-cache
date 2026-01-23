<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\PSR16;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\Clock\ClockInterface;

final class Expire
{
	/**
	 * count timestamp
	 *
	 * @return ($ttl is null ? null : int)
	 */
	public static function at(int|null|DateInterval|DateTimeInterface $ttl, ?ClockInterface $clock = null): ?int
	{
		return self::toDate($ttl, $clock)?->getTimestamp();
	}

	/**
	 * @return ($ttl is null ? null : DateTimeInterface)
	 */
	public static function toDate(
		int|null|DateInterval|DateTimeInterface $ttl,
		?ClockInterface $clock = null,
	): ?DateTimeInterface {
		if ($ttl === null) {
			return null;
		} elseif (is_int($ttl)) {
			return self::createDateTimeImmutable($clock)->modify("$ttl seconds");
		} elseif ($ttl instanceof DateTimeInterface) {
			return $ttl;
		}

		return self::createDateTimeImmutable($clock)->add($ttl);
	}

	private static function createDateTimeImmutable(?ClockInterface $clock = null): DateTimeImmutable
	{
		return $clock === null ? new DateTimeImmutable() : $clock->now();
	}

	/**
	 * count ttl
	 *
	 * @return ($ttl is null ? null : int)
	 */
	public static function after(int|null|DateInterval|DateTimeInterface $ttl, ?ClockInterface $clock = null): ?int
	{
		if ($ttl === null || is_int($ttl)) {
			return $ttl;
		}

		$now = self::createDateTimeImmutable($clock);
		if ($ttl instanceof DateTimeInterface) {
			$timestamp = $ttl->getTimestamp();
		} else {
			$timestamp = $now->add($ttl)->getTimestamp();
		}

		return $timestamp - $now->getTimestamp();
	}

	/**
	 * ttl to next day 00:00:00
	 */
	public static function midnight(?ClockInterface $clock = null): int
	{
		$now = $clock?->now()->modify('tomorrow') ?? new DateTimeImmutable('tomorrow');
		return self::after($now, $clock);
	}

	/**
	 * ttl to 23:59:59
	 */
	public static function lastSecondOfDay(?ClockInterface $clock = null): int
	{
		return self::midnight($clock) - 1;
	}

}
