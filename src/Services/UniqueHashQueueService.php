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

	public function execute(UniqueValueServiceInterface $checkUniqueValue, ?object $dataSet = null): string
	{
		$cacheKey = self::makeCacheKey($checkUniqueValue, $dataSet);
		return $this->cache->synchronized($cacheKey, function (CacheInterface $cache) use (
			$checkUniqueValue,
			$cacheKey,
			$dataSet,
		): string {
			/** @var list<non-empty-string> $values */
			$values = $cache->get($cacheKey) ?? [];
			if ($values === []) {
				$values = $this->uniqueValuesGeneratorService->execute($checkUniqueValue, $dataSet);
			}

			$value = array_pop($values);
			$cache->set($cacheKey, $values, $checkUniqueValue->ttl());

			return $value;
		});
	}

	public function count(UniqueValueServiceInterface $checkUniqueValue, ?object $dataSet = null): int
	{
		$values = $this->cache->get(self::makeCacheKey($checkUniqueValue, $dataSet));
		if (is_array($values) === false) {
			return 0;
		}

		return count($values);
	}

	public function saveNewBatch(UniqueValueServiceInterface $checkUniqueValue, ?object $dataSet = null): void
	{
		$this->cache->set(
			self::makeCacheKey($checkUniqueValue, $dataSet),
			$this->uniqueValuesGeneratorService->execute($checkUniqueValue, $dataSet),
		);
	}

	private static function makeCacheKey(UniqueValueServiceInterface $checkUniqueValue, ?object $dataSet): string
	{
		if ($dataSet === null) {
			return $checkUniqueValue::class;
		}

		return $checkUniqueValue::class . implode("\x00", get_object_vars($dataSet));
	}
}
