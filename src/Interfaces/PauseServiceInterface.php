<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Interfaces;

use DateInterval;

/**
 * @template T
 */
interface PauseServiceInterface
{
	/**
	 * @return T not null
	 */
	public function execute(): mixed;

	public function getCacheTtl(): DateInterval|int;

	/**
	 * @param T $value
	 * @return bool use default true
	 */
	public function wait(mixed $value): bool;
}
