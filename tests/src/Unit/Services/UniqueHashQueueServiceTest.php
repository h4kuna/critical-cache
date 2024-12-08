<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit\Services;

use h4kuna\CriticalCache\Nette\Storage\MemoryTtlStorage;
use h4kuna\CriticalCache\PSR16\Locking\CacheLock;
use h4kuna\CriticalCache\Services\UniqueHashQueueService;
use h4kuna\CriticalCache\Services\UniqueValuesGeneratorService;
use h4kuna\CriticalCache\Tests\Mock\UniqueValueServiceMock;
use h4kuna\CriticalCache\Tests\Mock\LockOriginalMock;
use Nette\Bridges\Psr\PsrCacheAdapter;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../bootstrap.php';

final class UniqueHashQueueServiceTest extends TestCase
{
	public function testExecute(): void
	{
		$cacheLock = new CacheLock(new PsrCacheAdapter(new MemoryTtlStorage()), new LockOriginalMock());
		$service = new UniqueHashQueueService($cacheLock, new UniqueValuesGeneratorService());

		$uniqueGenerator = new UniqueValueServiceMock([
			'B',
			'C',
			'E',
			'J',
		]);

		$value = $service->execute($uniqueGenerator);
		Assert::same('D', $value);

		Assert::same(1, $service->count($uniqueGenerator));

		$value = $service->execute($uniqueGenerator);
		Assert::same('A', $value);

		$service->saveNewBatch($uniqueGenerator);
		Assert::same($service->count($uniqueGenerator), 3); // not 3 because E is missing

		$value = $service->execute($uniqueGenerator);
		Assert::same('H', $value);
	}
}

(new UniqueHashQueueServiceTest())->run();
