<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Mock;

use h4kuna\CriticalCache\Interfaces\RandomGeneratorInterface;
use Tester\Assert;

final class RandomGeneratorMock implements RandomGeneratorInterface
{
	private string $counter = 'A';

	public function __construct(private ?DataSetEntity $dataSet = null) { }

	public function execute(?object $dataSet = null): string
	{
		Assert::same($dataSet, $this->dataSet);

		/** @var non-empty-string $char */
		$char = $this->counter;
		$this->counter++;

		return $char;
	}

}
