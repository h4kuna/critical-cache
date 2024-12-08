<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Contracts;

use h4kuna\CriticalCache\Interfaces\UniqueValueServiceInterface;

interface UniqueDataStoreServiceContract
{
	public function execute(UniqueValueServiceInterface $checkUniqueValue): string;

	public function count(UniqueValueServiceInterface $checkUniqueValue): int;

	public function saveNewBatch(UniqueValueServiceInterface $checkUniqueValue): void;
}
