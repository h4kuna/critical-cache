<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Contracts;

use h4kuna\CriticalCache\Exceptions\GenerateUniqueDataFailedException;
use h4kuna\CriticalCache\Interfaces\UniqueValueServiceInterface;

/**
 * @template T of object=object
 * @phpstan-type TObject T
 */
interface UniqueHashQueueServiceContract
{
	/**
	 * @param T|null $dataSet
	 *
	 * @return non-empty-string
	 * @throws GenerateUniqueDataFailedException
	 */
	public function execute(UniqueValueServiceInterface $checkUniqueValue, ?object $dataSet = null): string;

	/**
	 * @param T|null $dataSet
	 */
	public function count(UniqueValueServiceInterface $checkUniqueValue, ?object $dataSet = null): int;

	/**
	 * @param T|null $dataSet
	 * @throws GenerateUniqueDataFailedException
	 */
	public function saveNewBatch(UniqueValueServiceInterface $checkUniqueValue, ?object $dataSet = null): void;
}
