<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Services;

use h4kuna\CriticalCache\Contracts\UniqueDataStoreServiceContract;
use h4kuna\CriticalCache\Contracts\UniqueValuesGeneratorServiceContract;
use h4kuna\CriticalCache\Interfaces\UniqueValueServiceInterface;
use h4kuna\CriticalCache\PSR16\CacheLocking;
use Psr\SimpleCache\CacheInterface;

final readonly class UniqueHashQueueService implements UniqueDataStoreServiceContract
{
	public function __construct(
		private CacheLocking $cache,
		private UniqueValuesGeneratorServiceContract $uniqueValuesGeneratorService,
	) {
	}

	public function execute(UniqueValueServiceInterface $checkUniqueValue): string
	{
		$cacheKey = $checkUniqueValue::class . $checkUniqueValue->cacheSuffix();
		return $this->cache->synchronized($cacheKey, function (CacheInterface $cache) use (
			$checkUniqueValue,
			$cacheKey,
		): string {
			/** @var list<non-empty-string> $values */
			$values = $cache->get($cacheKey) ?? [];
			if ($values === []) {
				$values = $this->uniqueValuesGeneratorService->execute($checkUniqueValue);
			}

			$value = array_pop($values);
			$cache->set($cacheKey, $values, $checkUniqueValue->ttl());

			return $value;
		});
	}

	public function count(UniqueValueServiceInterface $checkUniqueValue): int
	{
		$values = $this->cache->get($checkUniqueValue::class);
		if (is_array($values) === false) {
			return 0;
		}

		return count($values);
	}

	public function saveNewBatch(UniqueValueServiceInterface $checkUniqueValue): void
	{
		$this->cache->set(
			$checkUniqueValue::class,
			$this->uniqueValuesGeneratorService->execute($checkUniqueValue),
		);
	}
}
