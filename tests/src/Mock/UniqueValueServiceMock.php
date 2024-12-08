<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Mock;

use h4kuna\CriticalCache\Contracts\RandomGeneratorContract;
use h4kuna\CriticalCache\Interfaces\UniqueValueServiceInterface;

final class UniqueValueServiceMock implements UniqueValueServiceInterface
{
	/**
	 * @param list<string> $stored
	 */
	public function __construct(
		private array $stored,
		private RandomGeneratorContract $randomGenerator = new RandomGeneratorMock(),
	) {
	}

	public function check(array $data): iterable
	{
		$out = array_intersect($this->stored, $data);
		array_push($this->stored, ...array_values(array_diff($data, $out)));

		return $out;
	}

	public function getQueueSize(): int
	{
		return 4;
	}

	public function getRandomGenerator(): RandomGeneratorContract
	{
		return $this->randomGenerator;
	}

	public function getTries(): ?int
	{
		return null;
	}

}
