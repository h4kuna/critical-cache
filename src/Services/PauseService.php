<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Services;

use DateInterval;
use DateTimeImmutable;
use h4kuna\CriticalCache\Interfaces\PauseServiceInterface;
use h4kuna\DataType\Date\Interval;
use h4kuna\DataType\Date\Sleep;
use Psr\Clock\ClockInterface;

/**
 * @implements PauseServiceInterface<DateTimeImmutable>
 */
abstract class PauseService implements PauseServiceInterface
{
	public function __construct(
		private readonly ClockInterface $clock,
		private readonly DateInterval|int $pauseSeconds = 30)
	{
	}

	public function execute(): mixed
	{
		$this->run();

		return $this->clock
			->now()
			->modify(sprintf('+%d seconds',
				is_int($this->pauseSeconds) ? $this->pauseSeconds : Interval::toSeconds($this->pauseSeconds),
			));
	}

	public function getCacheTtl(): DateInterval|int
	{
		return $this->pauseSeconds;
	}

	public function wait(mixed $value): bool
	{
		Sleep::seconds(
			Interval::toSecondsMilli(
				$this->clock->now()->diff($value),
			),
		);

		return true;
	}

	abstract protected function run(): void;
}
