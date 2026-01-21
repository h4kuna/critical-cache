<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit\PSR16;

use Beste\Clock\FrozenClock;
use Closure;
use DateInterval;
use DateTimeImmutable;
use h4kuna\CriticalCache\PSR16\Expire;
use h4kuna\CriticalCache\Tests\Mock\ClockFrozen;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class ExpireTest extends TestCase
{
	/**
	 * @return array<string|int, array{0: Closure(static):void}>
	 */
	public static function dataAfter(): array
	{
		return [
			[
				static function (self $self) {
					$self->assert(null, null);
				},
			],
			[
				static function (self $self) {
					$self->assert(1, 1);
				},
			],
			[
				static function (self $self) {
					$self->assert(86400, new DateInterval('P1D'));
				},
			],
			[
				static function (self $self) {
					$self->assert(94694400, new DateInterval('P3Y'));
				},
			],
			[
				static function (self $self) {
					$self->assert(5, (new DateTimeImmutable())->setTimestamp(ClockFrozen::Time)->modify('+5 seconds'));
				},
			],
		];
	}

	public function assert(?int $expected, int|null|DateInterval|DateTimeImmutable $ttl): void
	{
		$factory = new ClockFrozen();
		Assert::same($expected, Expire::after($ttl, $factory));
		$expectedAt = $expected === null ? null : $expected + ClockFrozen::Time;
		Assert::same($expectedAt, Expire::at($ttl, $factory));
	}

	/**
	 * @param Closure(static):void $assert
	 *
	 * @dataProvider dataAfter
	 */
	public function testBasic(Closure $assert): void
	{
		$assert($this);
	}

	public function testMidnight(): void
	{
		$ttl = Expire::midnight(FrozenClock::at(new DateTimeImmutable('2020-12-11 10:09:08')));
		Assert::same(49852, $ttl);

		$ttl = Expire::midnight(FrozenClock::at(new DateTimeImmutable('2020-12-11 23:59:59')));
		Assert::same(1, $ttl);

		$ttl = Expire::midnight(FrozenClock::at(new DateTimeImmutable('2020-12-12 00:00:00')));
		Assert::same(86400, $ttl);
	}
}

(new ExpireTest())->run();
