<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Services;

use h4kuna\CriticalCache\Interfaces\RandomGeneratorInterface;
use h4kuna\CriticalCache\Interfaces\UniqueValueServiceInterface;

abstract class UniqueValueServiceAbstract implements UniqueValueServiceInterface
{
	/**
	 * @param positive-int $queueSize
	 * @param int<1, 10> $tries
	 */
	public function __construct(
		private readonly RandomGeneratorInterface $randomGenerator,
		private readonly int $queueSize = 50,
		private readonly int $tries = 3,
		private readonly int $ttl = 2592000, // 60 * 60 * 24 * 30
	) {
	}

	public function getQueueSize(): int
	{
		return $this->queueSize;
	}

	public function getRandomGenerator(): RandomGeneratorInterface
	{
		return $this->randomGenerator;
	}

	public function getTries(): int
	{
		return $this->tries;
	}

	public function transform(array $data): array
	{
		return array_values($data);
	}

	public function ttl(): int
	{
		return $this->ttl;
	}
}
