<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Mock;

use h4kuna\CriticalCache\Contracts\RandomGeneratorContract;
use h4kuna\CriticalCache\Services\UniqueValueServiceAbstract;

final class UniqueValueServiceMock extends UniqueValueServiceAbstract
{
	/**
	 * @param list<string> $stored
	 */
	public function __construct(
		private array $stored,
		RandomGeneratorContract $randomGenerator = new RandomGeneratorMock(),
	) {
		parent::__construct($randomGenerator, 4);
	}

	public function check(array $data): iterable
	{
		$out = array_intersect($this->stored, $data);
		array_push($this->stored, ...array_values(array_diff($data, $out)));

		return $out;
	}
}
