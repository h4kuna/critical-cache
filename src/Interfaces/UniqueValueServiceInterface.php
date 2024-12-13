<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Interfaces;

use h4kuna\CriticalCache\Contracts\RandomGeneratorContract;

interface UniqueValueServiceInterface
{
	/**
	 * Intersect between generated list and stored list.
	 *
	 * You have stored [A, D]
	 * Generator generate list $data [A, B, C]
	 * return [A]
	 *
	 * @param array<string> $data
	 *
	 * @return iterable<int, string> return non duplicity
	 */
	public function check(array $data): iterable;

	/**
	 * for began choose between 20 and 100
	 *
	 * @return positive-int
	 */
	public function getQueueSize(): int;

	public function getRandomGenerator(): RandomGeneratorContract;

	/**
	 * by default return 3
	 *
	 * @return int<1, 10>
	 */
	public function getTries(): int;
}
