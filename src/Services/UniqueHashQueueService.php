<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Services;

use h4kuna\CriticalCache\Contracts\UniqueDataStoreServiceContract;
use h4kuna\CriticalCache\Contracts\UniqueValuesGeneratorServiceContract;
use h4kuna\CriticalCache\Interfaces\CheckUniqueValueInterface;
use h4kuna\CriticalCache\PSR16\CacheLocking;
use Psr\SimpleCache\CacheInterface;

final readonly class UniqueHashQueueService implements UniqueDataStoreServiceContract
{
	public function __construct(
		private CacheLocking $cache,
		private UniqueValuesGeneratorServiceContract $uniqueValuesGeneratorService,
	) {
	}

	public function execute(
		CheckUniqueValueInterface $checkUniqueValue,
		int $queueSize = 100,
		?callable $randomizer = null,
		int $tries = 3,
	): string {
		$cacheKey = $checkUniqueValue::class;
		return $this->cache->synchronized($cacheKey, function (CacheInterface $cache) use (
			$randomizer,
			$checkUniqueValue,
			$cacheKey,
			$queueSize,
			$tries,
		): string {
			/** @var list<non-empty-string> $values */
			$values = $cache->get($cacheKey) ?? [];
			if ($values === []) {
				$values = $this->uniqueValuesGeneratorService->execute($checkUniqueValue, $queueSize, $randomizer, $tries);
			}

			$value = array_pop($values);
			$cache->set($cacheKey, $values);

			return $value;
		});
	}

	public function count(CheckUniqueValueInterface $checkUniqueValue): int
	{
		$values = $this->cache->get($checkUniqueValue::class);
		if (is_array($values) === false) {
			return 0;
		}

		return count($values);
	}

	public function saveNewBatch(
		CheckUniqueValueInterface $checkUniqueValue,
		int $queueSize = 100,
		?callable $randomizer = null,
		int $tries = 3,
	): void {
		$this->cache->set(
			$checkUniqueValue::class,
			$this->uniqueValuesGeneratorService->execute($checkUniqueValue, $queueSize, $randomizer, $tries),
		);
	}
}
