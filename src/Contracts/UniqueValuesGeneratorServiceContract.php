<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Contracts;

use h4kuna\CriticalCache\Interfaces\CheckUniqueValueInterface;

interface UniqueValuesGeneratorServiceContract
{
	/**
	 * @param ?callable(): string $randomizer
	 * @return non-empty-list<string>
	 */
	public function execute(
		CheckUniqueValueInterface $checkUniqueColumnQuery,
		int $queueSize,
		?callable $randomizer = null,
		int $tries = 3,
	): array;
}
