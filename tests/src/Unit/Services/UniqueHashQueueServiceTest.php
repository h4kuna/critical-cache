<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit\Services;

use Closure;
use h4kuna\CriticalCache\Nette\Storage\MemoryTtlStorage;
use h4kuna\CriticalCache\PSR16\Locking\CacheLock;
use h4kuna\CriticalCache\Services\UniqueHashQueueService;
use h4kuna\CriticalCache\Services\UniqueValuesGeneratorService;
use h4kuna\CriticalCache\Tests\Mock\DataSetEntity;
use h4kuna\CriticalCache\Tests\Mock\LockOriginalMock;
use h4kuna\CriticalCache\Tests\Mock\RandomGeneratorMock;
use h4kuna\CriticalCache\Tests\Mock\UniqueValueServiceMock;
use Nette\Bridges\Psr\PsrCacheAdapter;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../bootstrap.php';

final class UniqueHashQueueServiceTest extends TestCase
{
	/**
	 * @return array<string|int, array{0: Closure(static):void}>
	 */
	public static function dataExecute(): array
	{
		return [
			[
				static function (self $self) {
					$self->assertExecute(
						new DataSetEntity('lorem', 42),
					);
				},
			],
			[
				static function (self $self) {
					$self->assertExecute(
						null,
					);
				},
			],
		];
	}

	/**
	 * @param Closure(static):void $assert
	 * @dataProvider dataExecute
	 */
	public function testExecute(Closure $assert): void
	{
		$assert($this);
	}

	public function assertExecute(
		?DataSetEntity $dataSet,
	): void {
		$cacheLock = new CacheLock(new PsrCacheAdapter(new MemoryTtlStorage()), new LockOriginalMock());
		$service = new UniqueHashQueueService($cacheLock, new UniqueValuesGeneratorService());

		$uniqueGenerator = new UniqueValueServiceMock([
			'B',
			'C',
			'E',
			'J',
		], new RandomGeneratorMock($dataSet));

		$value = $service->execute($uniqueGenerator, $dataSet);
		Assert::same('D', $value);

		Assert::same(1, $service->count($uniqueGenerator, $dataSet));

		$value = $service->execute($uniqueGenerator, $dataSet);
		Assert::same('A', $value);

		$service->saveNewBatch($uniqueGenerator, $dataSet);
		Assert::same($service->count($uniqueGenerator, $dataSet), 3); // not 3 because E is missing

		$value = $service->execute($uniqueGenerator, $dataSet);
		Assert::same('H', $value);
	}
}

(new UniqueHashQueueServiceTest())->run();
