<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Mock;

use h4kuna\CriticalCache\Contracts\RandomGeneratorContract;

final class RandomGeneratorMock implements RandomGeneratorContract
{
	private string $counter = 'A';

	public function execute(): string
	{
		$char = $this->counter;
		$this->counter++;

		return $char;
	}

}
