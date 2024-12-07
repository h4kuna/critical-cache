<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Mock;

use h4kuna\CriticalCache\Interfaces\CheckUniqueValueInterface;

final class CheckUniqueValueMock implements CheckUniqueValueInterface
{
	/**
	 * @param list<string> $stored
	 */
	public function __construct(private array $stored)
	{
	}

	public function execute(array $data): iterable
	{
		$out = array_intersect($this->stored, $data);
		array_push($this->stored, ...array_values(array_diff($data, $out)));

		return $out;
	}

}
