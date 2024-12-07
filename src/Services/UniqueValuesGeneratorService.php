<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Services;

use h4kuna\CriticalCache\Contracts\RandomGeneratorContract;
use h4kuna\CriticalCache\Contracts\UniqueValuesGeneratorServiceContract;
use h4kuna\CriticalCache\Exceptions\GenerateUniqueDataFailedException;
use h4kuna\CriticalCache\Interfaces\CheckUniqueValueInterface;

final readonly class UniqueValuesGeneratorService implements UniqueValuesGeneratorServiceContract
{
	/**
	 * @param positive-int $maxUniqueFailed
	 */
	public function __construct(
		private RandomGeneratorContract $randomGenerator,
		private int $maxUniqueFailed = 100_000,
	) {
	}

	public function execute(
		CheckUniqueValueInterface $checkUniqueColumnQuery,
		int $queueSize = 100,
		?callable $randomizer = null,
		int $tries = 3,
	): array {
		$randomizer ??= $this->randomGenerator->generate(...);
		$values = [];
		for ($i = 0; $i < $tries; ++$i) {
			$values = $this->newRandomBatch($randomizer, $checkUniqueColumnQuery, $queueSize);
			if ($values !== []) {
				break;
			}
		}
		if ($values === []) {
			throw new GenerateUniqueDataFailedException(sprintf('Empty unique data, after "%s" tries.', $tries));
		}

		return $values;
	}

	/**
	 * @param callable(): string $randomizer
	 * @return list<non-empty-string>
	 */
	private function newRandomBatch(
		callable $randomizer,
		CheckUniqueValueInterface $checkUniqueColumnQuery,
		int $size,
	): array {
		$new = [];
		$i = 0;
		$stopUnique = 0;
		do {
			$v = $randomizer();
			if (isset($new[$v])) {
				++$stopUnique;
				if ($stopUnique >= $this->maxUniqueFailed) {
					throw new GenerateUniqueDataFailedException(sprintf('Really bad unique generator. It has %s same values.', $stopUnique));
				}
				continue;
			}
			++$i;
			$new[$v] = $v;
		} while ($i < $size);

		foreach ($checkUniqueColumnQuery->execute($new) as $uuid) {
			unset($new[$uuid]);
		}

		/** @var list<non-empty-string> $out */
		$out = array_values($new);

		return $out;
	}
}
