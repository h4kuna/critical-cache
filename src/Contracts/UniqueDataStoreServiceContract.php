<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Contracts;

use h4kuna\CriticalCache\Interfaces\CheckUniqueValueInterface;

interface UniqueDataStoreServiceContract
{
	/**
	 * @param ?callable(): string $randomizer
	 */
	public function execute(
		CheckUniqueValueInterface $checkUniqueValue,
		int $queueSize = 100,
		?callable $randomizer = null,
		int $tries = 3,
	): string;

	public function count(CheckUniqueValueInterface $checkUniqueValue): int;

	/**
	 * @param ?callable(): string $randomizer
	 */
	public function saveNewBatch(
		CheckUniqueValueInterface $checkUniqueValue,
		int $queueSize = 100,
		?callable $randomizer = null,
		int $tries = 3,
	): void;
}
