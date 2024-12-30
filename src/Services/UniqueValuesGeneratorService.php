<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Services;

use h4kuna\CriticalCache\Contracts\UniqueValuesGeneratorServiceContract;
use h4kuna\CriticalCache\Exceptions\GenerateUniqueDataFailedException;
use h4kuna\CriticalCache\Interfaces\UniqueValueServiceInterface;

final readonly class UniqueValuesGeneratorService implements UniqueValuesGeneratorServiceContract
{
	/**
	 * @param positive-int $maxUniqueFailed
	 */
	public function __construct(
		private int $maxUniqueFailed = 100_000,
	) {
	}

	public function execute(UniqueValueServiceInterface $checkUniqueColumnQuery): array
	{
		$tries = $checkUniqueColumnQuery->getTries();
		$values = [];
		for ($i = 0; $i < $tries; ++$i) {
			$values = $this->newRandomBatch($checkUniqueColumnQuery);
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
	 * @return list<non-empty-string>
	 */
	private function newRandomBatch(UniqueValueServiceInterface $checkUniqueColumnQuery): array
	{
		$size = $checkUniqueColumnQuery->getQueueSize();
		$randomizer = $checkUniqueColumnQuery->getRandomGenerator();
		$new = [];
		$i = 0;
		$stopUnique = 0;
		do {
			$hash = $randomizer->execute();
			if (isset($new[$hash])) {
				++$stopUnique;
				if ($stopUnique >= $this->maxUniqueFailed) {
					throw new GenerateUniqueDataFailedException(sprintf('Really bad unique generator. It has %s same values.', $stopUnique));
				}
				continue;
			}
			++$i;
			$new[$hash] = $hash;
		} while ($i < $size);

		assert($new !== []);

		foreach ($checkUniqueColumnQuery->check($new) as $matchedHash) {
			unset($new[$matchedHash]);
		}

		return $checkUniqueColumnQuery->transform($new);
	}
}
