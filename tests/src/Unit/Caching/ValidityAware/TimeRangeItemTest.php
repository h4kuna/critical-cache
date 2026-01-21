<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit\Caching\ValidityAware;

use Beste\Clock\FrozenClock;
use DateTimeImmutable;
use h4kuna\CriticalCache\Caching\ValidityAware\TimeRangeItem;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../../bootstrap.php';

final class TimeRangeItemTest extends TestCase
{
	/**
	 * @dataProvider provideValidation
	 */
	public function testValidation(
		TimeRangeItem $timeRangeItem,
		bool $isValid,
		bool $isExpired,
		?string $value): void
	{
		Assert::same($isValid, $timeRangeItem->isValid());
		Assert::same($isExpired, $timeRangeItem->isExpired());
		Assert::same($value, $timeRangeItem->value());
	}

	/**
	 * @return array<mixed>
	 */
	public static function provideValidation(): array
	{
		return [
			[self::createTimeRangeItem(null, null, null), false, true, null],
			[self::createTimeRangeItem(new DateTimeImmutable('2020-12-30 10:13:16'), null, '1'), false, false, null],
			[self::createTimeRangeItem(new DateTimeImmutable('2020-12-30 10:13:15'), null, '1'), true, false, '1'],
			[self::createTimeRangeItem(new DateTimeImmutable('2020-12-30 10:13:13'), new DateTimeImmutable('2020-12-30 10:13:14'), '1'), false, true, null],
			[self::createTimeRangeItem(new DateTimeImmutable('2020-12-30 10:13:14'), new DateTimeImmutable('2020-12-30 10:13:15'), '1'), true, false, '1'],
			[self::createTimeRangeItem(new DateTimeImmutable('2020-12-30 10:13:15'), new DateTimeImmutable('2020-12-30 10:13:16'), '1'), true, false, '1'],
			[self::createTimeRangeItem(new DateTimeImmutable('2020-12-30 10:13:16'), new DateTimeImmutable('2020-12-30 10:13:17'), '1'), false, false, null],
			[self::createTimeRangeItem(null, new DateTimeImmutable('2020-12-30 10:13:14'), '1'), false, true, null],
			[self::createTimeRangeItem(null, new DateTimeImmutable('2020-12-30 10:13:15'), '1'), true, false, '1'],
		];
	}

	private static function createTimeRangeItem(
		?DateTimeImmutable $from,
		?DateTimeImmutable $to,
		?string $value,
	): TimeRangeItem {
		return new TimeRangeItem($from, $to, $value, FrozenClock::at(new DateTimeImmutable('2020-12-30 10:13:15')));
	}
}

(new TimeRangeItemTest())->run();
