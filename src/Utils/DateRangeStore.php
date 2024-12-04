<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Utils;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * @phpstan-type TypeRange array{from: ?DateTimeImmutable, to: ?DateTimeImmutable, v: string}
 * @phpstan-type TypeSave array{from?: string, to?: string, v?: string}
 */
final class DateRangeStore
{
	/**
	 * @param TypeSave $data
	 * @return TypeRange
	 */
	public static function decode(array $data): array
	{
		$data['from'] = self::toDate($data['from'] ?? '');
		$data['to'] = self::toDate($data['to'] ?? '');
		if (isset($data['v']) === false) {
			$data['v'] = '';
		}

		return $data;
	}

	/**
	 * @return TypeSave
	 */
	public static function encode(
		?DateTimeInterface $from,
		DateTimeInterface $to,
		string $value,
	): array {
		$out = [
			'to' => self::toString($to),
		];

		if ($from !== null) {
			$out['from'] = self::toString($from);
		}

		if ($value !== '') {
			$out['v'] = $value;
		}

		return $out;
	}

	private static function toString(?DateTimeInterface $dateTime): string
	{
		return $dateTime === null ? '' : $dateTime->format(DateTimeInterface::RFC3339_EXTENDED);
	}

	private static function toDate(string $dateTime): ?DateTimeImmutable
	{
		if ($dateTime === '') {
			return null;
		}
		$date = DateTimeImmutable::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $dateTime);

		return $date === false ? null : $date;
	}
}
