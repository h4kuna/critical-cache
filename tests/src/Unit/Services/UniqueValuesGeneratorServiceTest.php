<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit\Services;

use h4kuna\CriticalCache\Exceptions\GenerateUniqueDataFailedException;
use h4kuna\CriticalCache\Services\UniqueValuesGeneratorService;
use h4kuna\CriticalCache\Tests\Mock\CheckUniqueValueMock;
use h4kuna\CriticalCache\Tests\Mock\RandomGeneratorMock;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../bootstrap.php';

final class UniqueValuesGeneratorServiceTest extends TestCase
{
	public function testBasic(): void
	{
		$service = new UniqueValuesGeneratorService(new RandomGeneratorMock());

		$checkUnique = new CheckUniqueValueMock(['C', 'D', 'G']);
		$values = $service->execute($checkUnique, 5);
		Assert::same(['A', 'B', 'E'], $values);

		$values = $service->execute($checkUnique, 4);
		Assert::same(['F', 'H', 'I'], $values);

		Assert::exception(function () use ($service, $checkUnique) {
			$queue = ['A', 'B', 'E', 'F', 'H', 'I'];
			$service->execute($checkUnique, 5, static function () use (&$queue): string {
				start:
				$value = current($queue);
				next($queue);
				if ($value === false) { // @phpstan-ignore-line
					reset($queue);
					goto start;
				}
				return $value;
			});
		}, GenerateUniqueDataFailedException::class, 'Empty unique data, after "3" tries.');
	}

	public function testCheckBadGenerator(): void
	{
		$service = new UniqueValuesGeneratorService(new RandomGeneratorMock());

		$checkUnique = new CheckUniqueValueMock(['C', 'D', 'G']);

		Assert::exception(function () use ($service, $checkUnique) {
			$service->execute($checkUnique, 5, static fn (): string => 'A');
		}, GenerateUniqueDataFailedException::class, 'Really bad unique generator. It has 100000 same values.');
	}
}

(new UniqueValuesGeneratorServiceTest)->run();
