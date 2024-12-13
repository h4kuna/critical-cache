<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Services;

use h4kuna\CriticalCache\Contracts\RandomGeneratorContract;
use h4kuna\CriticalCache\Interfaces\UniqueValueServiceInterface;

abstract class UniqueValueServiceAbstract implements UniqueValueServiceInterface
{
	/**
	 * @param positive-int $queueSize
	 * @param int<1, 10> $tries
	 */
	public function __construct(
		private readonly RandomGeneratorContract $randomGenerator,
		private readonly int $queueSize = 50,
		private readonly int $tries = 3,
	) {
	}

	public function getQueueSize(): int
	{
		return $this->queueSize;
	}

	public function getRandomGenerator(): RandomGeneratorContract
	{
		return $this->randomGenerator;
	}

	public function getTries(): int
	{
		return $this->tries;
	}

}
