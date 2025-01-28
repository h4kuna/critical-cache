<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Mock;

use h4kuna\CriticalCache\Interfaces\RandomGeneratorInterface;

final class RandomGeneratorMock implements RandomGeneratorInterface
{
	private string $counter = 'A';

	public function execute(?object $dataSet = null): string
	{
		/** @var non-empty-string $char */
		$char = $this->counter;
		$this->counter++;

		return $char;
	}

}
