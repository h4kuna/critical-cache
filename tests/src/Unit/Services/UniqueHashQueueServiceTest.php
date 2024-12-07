<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit\Services;

use h4kuna\CriticalCache\Nette\Storage\MemoryTtlStorage;
use h4kuna\CriticalCache\PSR16\Locking\CacheLock;
use h4kuna\CriticalCache\Services\UniqueHashQueueService;
use h4kuna\CriticalCache\Services\UniqueValuesGeneratorService;
use h4kuna\CriticalCache\Tests\Mock\CheckUniqueValueMock;
use h4kuna\CriticalCache\Tests\Mock\LockOriginalMock;
use h4kuna\CriticalCache\Tests\Mock\RandomGeneratorMock;
use Nette\Bridges\Psr\PsrCacheAdapter;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../bootstrap.php';

final class UniqueHashQueueServiceTest extends TestCase
{
	public function testExecute(): void
	{
		$cacheLock = new CacheLock(new PsrCacheAdapter(new MemoryTtlStorage()), new LockOriginalMock());
		$service = new UniqueHashQueueService($cacheLock, new UniqueValuesGeneratorService(new RandomGeneratorMock()));

		$uniqueGenerator = new CheckUniqueValueMock([
			'B',
			'C',
			'E',
			'J',
		]);

		$generate = static fn () => $service->execute($uniqueGenerator, 3, null, 2);

		$value = $generate();
		Assert::same('A', $value);

		$value = $generate();
		Assert::same('F', $value);

		Assert::same(1, $service->count($uniqueGenerator));

		$service->saveNewBatch( $uniqueGenerator);
		Assert::same($service->count($uniqueGenerator), 99); // not 100 because J is missing

		$value = $generate();
		Assert::same('DB', $value);
	}
}

(new UniqueHashQueueServiceTest())->run();
