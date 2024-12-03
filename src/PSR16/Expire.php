<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\PSR16;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\Clock\ClockInterface;

/**
 * @phpstan-type TypeKey string|int|float|list<string|int|float>
 */
final class Expire
{
	public static function at(int|null|DateInterval|DateTimeInterface $ttl, ?ClockInterface $clock = null): ?int
	{
		return self::toDate($ttl, $clock)?->getTimestamp();
	}

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
	 * @param TypeKey $key
	 */
	public static function key(string|int|float|array $key): string
	{
		return implode("\x00", (array) $key);
	}

}
