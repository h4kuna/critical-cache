<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit\Services;

use h4kuna\CriticalCache\Interfaces\RandomGeneratorInterface;
use h4kuna\CriticalCache\Exceptions\GenerateUniqueDataFailedException;
use h4kuna\CriticalCache\Interfaces\UniqueValueServiceInterface;
use h4kuna\CriticalCache\Services\UniqueValueServiceAbstract;
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
		return new class extends UniqueValueServiceAbstract {

			public function __construct() {
				parent::__construct(new RandomGeneratorMock(), 3);
			}

			public function check(array $data, ?object $dataSet = null): iterable
			{
				return array_values($data);
			}
		};
	}

	private static function createBadGenerator(): UniqueValueServiceInterface
	{
		return new class extends UniqueValueServiceAbstract {
			public function __construct()
			{
				$randomGenerator = new class implements RandomGeneratorInterface {
					public function execute(?object $dataSet = null): string
					{
						return 'A';
					}
				};

				parent::__construct($randomGenerator, 3);
			}

			public function check(array $data, ?object $dataSet = null): iterable
			{
				return array_values($data);
			}
		};
	}
}

(new UniqueValuesGeneratorServiceTest)->run();
