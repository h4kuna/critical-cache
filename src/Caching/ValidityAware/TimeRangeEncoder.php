<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Caching\ValidityAware;

use DateTimeImmutable;
use DateTimeInterface;
use h4kuna\CriticalCache\Exceptions\LogicException;
use Psr\Clock\ClockInterface;

/**
 * f = from
 * t = to
 * v = value
 *
 * @phpstan-type TypeSave array{f: string, t: string, v: string}
 */
final readonly class TimeRangeEncoder
{
	public function __construct(
		private ClockInterface $clock,
	) {
	}

	/**
	 * @param TypeSave|null $data
	 */
	public function decode(?array $data): TimeRangeItem
	{
		if ($data === null) {
			return new TimeRangeItem(from: null, to: null, value: null, clock: $this->clock);
		}

		return new TimeRangeItem(self::toDate($data['f']), self::toDate($data['t']), $data['v'], $this->clock);
	}

	/**
	 * @return TypeSave
	 */
	public function encode(
		?DateTimeInterface $from,
		DateTimeInterface $to,
		string $value,
	): array {
		return [
			't' => self::toString($to),
			'v' => $value,
			'f' => $from === null ? '' : self::toString($from),
		];
	}

	private static function toString(DateTimeInterface $dateTime): string
	{
		return $dateTime->format(DateTimeInterface::RFC3339_EXTENDED);
	}

	private static function toDate(string $dateTime): ?DateTimeImmutable
	{
		if ($dateTime === '') {
			return null;
		}
		$date = DateTimeImmutable::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $dateTime);

		if ($date === false) {
			throw new LogicException('Broken date format in cache.');
		}

		return $date;
	}
}
