<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Contracts;

use h4kuna\CriticalCache\Interfaces\UniqueValueServiceInterface;

/**
 * @phpstan-import-type TObject from UniqueHashQueueServiceContract
 */
interface UniqueValuesGeneratorServiceContract
{
	/**
	 * @param TObject|null $dataSet
	 *
	 * @return non-empty-list<non-empty-string>
	 */
	public function execute(UniqueValueServiceInterface $checkUniqueColumnQuery, ?object $dataSet = null): array;
}
