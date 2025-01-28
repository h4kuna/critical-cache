<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Contracts;

use h4kuna\CriticalCache\Interfaces\UniqueValueServiceInterface;

/**
 * @template T of object=object
 * @phpstan-type TObject T
 */
interface UniqueDataStoreServiceContract
{
	/**
	 * @param T|null $dataSet
	 */
	public function execute(UniqueValueServiceInterface $checkUniqueValue, ?object $dataSet = null): string;

	/**
	 * @param T|null $dataSet
	 */
	public function count(UniqueValueServiceInterface $checkUniqueValue, ?object $dataSet = null): int;

	/**
	 * @param T|null $dataSet
	 */
	public function saveNewBatch(UniqueValueServiceInterface $checkUniqueValue, ?object $dataSet = null): void;
}
