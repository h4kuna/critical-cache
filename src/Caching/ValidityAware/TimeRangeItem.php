<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Caching\ValidityAware;

use DateTimeImmutable;
use h4kuna\CriticalCache\Exceptions\LogicException;
use Psr\Clock\ClockInterface;

final readonly class TimeRangeItem
{
	public function __construct(
		public ?DateTimeImmutable $from,
		public ?DateTimeImmutable $to,
		public ?string $value,
		private ClockInterface $clock,
	) {
		if ($this->from !== null && $this->to !== null && $this->from > $this->to) {
			throw new LogicException('From date must be less than to date.');
		}
	}

	public function value(): ?string
	{
		return $this->isValid() ? $this->value : null;
	}

	public function isExpired(): bool
	{
		if ($this->value === null) {
			return true;
		} elseif ($this->to === null) {
			return false;
		}

		return $this->to < $this->clock->now();
	}

	public function isValid(): bool
	{
		if ($this->value === null) {
			return false;
		} elseif ($this->from === null && $this->to === null) {
			return true;
		}

		$now = $this->clock->now();
		if ($this->from !== null && $this->to !== null) {
			return $this->from <= $now && $now <= $this->to;
		} elseif ($this->from === null) {
			return $now <= $this->to;
		}

		return $this->from <= $now;
	}
}
