<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Interfaces;

use h4kuna\CriticalCache\Contracts\UniqueDataStoreServiceContract;

/**
 * @phpstan-import-type TObject from UniqueDataStoreServiceContract
 */
interface UniqueValueServiceInterface
{
	/**
	 * Intersect between generated list and stored list.
	 *
	 * You have stored [A, D]
	 * Generator generate list $data [A, B, C]
	 * return [A]
	 *
	 * @param non-empty-array<non-empty-string, non-empty-string> $data
	 * @param TObject|null $dataSet
	 *
	 * @return iterable<int, string> return non duplicity
	 */
	public function check(array $data, ?object $dataSet = null): iterable;

	/**
	 * for began choose between 20 and 100
	 *
	 * @return positive-int
	 */
	public function getQueueSize(): int;

	public function getRandomGenerator(): RandomGeneratorInterface;

	/**
	 * by default return 3
	 *
	 * @return int<1, 10>
	 */
	public function getTries(): int;

	/**
	 * use array_values by default, or if you need array_reverse
	 *
	 * @param array<non-empty-string, non-empty-string> $data
	 * @return list<non-empty-string>
	 */
	public function transform(array $data): array;

	/**
	 * seconds
	 */
	public function ttl(): int;
}
