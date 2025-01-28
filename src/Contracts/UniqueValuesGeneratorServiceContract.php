<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Contracts;

use h4kuna\CriticalCache\Interfaces\UniqueValueServiceInterface;

interface UniqueValuesGeneratorServiceContract
{
	/**
	 * @param object|null $dataSet - simple object
	 * @return non-empty-list<string>
	 */
	public function execute(UniqueValueServiceInterface $checkUniqueColumnQuery, ?object $dataSet = null): array;
}
