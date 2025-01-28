<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Interfaces;

use h4kuna\CriticalCache\Contracts\UniqueDataStoreServiceContract;

/**
 * @phpstan-import-type TObject from UniqueDataStoreServiceContract
 */
interface RandomGeneratorInterface
{
	/**
	 * @param TObject|null $dataSet
	 *
	 * @return non-empty-string
	 */
	public function execute(?object $dataSet = null): string;
}
