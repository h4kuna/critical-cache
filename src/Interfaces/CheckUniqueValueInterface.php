<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Interfaces;

interface CheckUniqueValueInterface
{
	/**
	 * Intersect between generated list and stored list.
	 *
	 * You have stored [A, D]
	 * Generator generate list $data [A, B, C]
	 * return [A]
	 *
	 * @param array<string> $data
	 *
	 * @return iterable<int, string> return non duplicity
	 */
	public function execute(array $data): iterable;
}
