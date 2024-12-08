<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit\Services;

use h4kuna\CriticalCache\Contracts\RandomGeneratorContract;
use h4kuna\CriticalCache\Exceptions\GenerateUniqueDataFailedException;
use h4kuna\CriticalCache\Interfaces\UniqueValueServiceInterface;
use h4kuna\CriticalCache\Services\UniqueValuesGeneratorService;
use h4kuna\CriticalCache\Tests\Mock\RandomGeneratorMock;
use h4kuna\CriticalCache\Tests\Mock\UniqueValueServiceMock;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../bootstrap.php';

final class UniqueValuesGeneratorServiceTest extends TestCase
{
	public function testSuccess(): void
	{
		$service = new UniqueValuesGeneratorService();
		$checkUnique = new UniqueValueServiceMock(['C', 'D', 'G']);
		$values = $service->execute($checkUnique);
		Assert::same(['A', 'B'], $values);

		$values = $service->execute($checkUnique);
		Assert::same(['E', 'F', 'H'], $values);
	}

	public function testFullyChecked(): void
	{
		$service = new UniqueValuesGeneratorService();

		Assert::exception(function () use ($service) {
			$service->execute(self::createFullyChecked());
		}, GenerateUniqueDataFailedException::class, 'Empty unique data, after "3" tries.');
	}

	public function testCheckBadGenerator(): void
	{
		$service = new UniqueValuesGeneratorService();

		Assert::exception(function () use ($service) {
			$service->execute(self::createBadGenerator());
		}, GenerateUniqueDataFailedException::class, 'Really bad unique generator. It has 100000 same values.');
	}

	private static function createFullyChecked(): UniqueValueServiceInterface
	{
		return new class implements UniqueValueServiceInterface {

			public function __construct(
				private RandomGeneratorContract $randomGenerator = new RandomGeneratorMock(),
			) {
			}

			public function check(array $data): iterable
			{
				return array_values($data);
			}

			public function getQueueSize(): int
			{
				return 3;
			}

			public function getRandomGenerator(): RandomGeneratorContract
			{
				return $this->randomGenerator;
			}

			public function getTries(): ?int
			{
				return null;
			}
		};
	}

	private static function createBadGenerator(): UniqueValueServiceInterface
	{
		return new class implements UniqueValueServiceInterface {
			private RandomGeneratorContract $randomGenerator;

			public function __construct()
			{
				$this->randomGenerator = new class implements RandomGeneratorContract {
					public function execute(): string
					{
						return 'A';
					}
				};
			}

			public function check(array $data): iterable
			{
				return array_values($data);
			}

			public function getQueueSize(): int
			{
				return 3;
			}

			public function getRandomGenerator(): RandomGeneratorContract
			{
				return $this->randomGenerator;
			}

			public function getTries(): ?int
			{
				return null;
			}
		};
	}
}

(new UniqueValuesGeneratorServiceTest)->run();