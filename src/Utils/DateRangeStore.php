<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Utils;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * @phpstan-type TypeRange array{from: ?DateTimeImmutable, to: ?DateTimeImmutable, v: string}
 */
final class DateRangeStore
{
	/**
	 * @return TypeRange
	 */
	public static function decode(string $value): array
	{
		if ($value === '') {
			return [
				'from' => null,
				'to' => null,
				'v' => '',
			];
		}

		/** @var array{from?: string, to?: string, v?: string} $data */
		$data = unserialize($value);
		$data['from'] = self::toDate($data['from'] ?? '');
		$data['to'] = self::toDate($data['to'] ?? '');
		if (isset($data['v']) === false) {
			$data['v'] = '';
		}

		return $data;
	}

	public static function toDate(string $dateTime): ?DateTimeImmutable
	{
		if ($dateTime === '') {
			return null;
		}
		$date = DateTimeImmutable::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $dateTime);

		return $date === false ? null : $date;
	}

	public static function encode(
		?DateTimeInterface $from,
		DateTimeInterface $to,
		string $value,
	): string {
		$out = [
			'to' => self::toString($to),
		];

		if ($from !== null) {
			$out['from'] = self::toString($from);
		}

		if ($value !== '') {
			$out['v'] = $value;
		}

		return serialize($out);
	}

	public static function toString(?DateTimeInterface $dateTime): string
	{
		return $dateTime === null ? '' : $dateTime->format(DateTimeInterface::RFC3339_EXTENDED);
	}
}
