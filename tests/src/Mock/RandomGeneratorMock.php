<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Tests\Mock;

use h4kuna\CriticalCache\Interfaces\RandomGeneratorInterface;
use Tester\Assert;
use function chr;
use function intdiv;
use function ord;

final class RandomGeneratorMock implements RandomGeneratorInterface
{

	private int $counter = 0;

	public function __construct(private ?DataSetEntity $dataSet = null)
	{
	}

	public function execute(?object $dataSet = null): string
	{
		Assert::same($dataSet, $this->dataSet);

		return self::toLetters($this->counter++);
	}

	/**
	 * 0 => A, 25 => Z, 26 => AA, 27 => AB, ... like string increment before PHP 8.5
	 *
	 * @return non-empty-string
	 */
	private static function toLetters(int $number): string
	{
		$letters = '';
		do {
			$letters = chr(ord('A') + $number % 26) . $letters;
			$number = intdiv($number, 26) - 1;
		} while ($number >= 0);

		return $letters;
	}

}
