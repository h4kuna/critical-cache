<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Contracts;

use h4kuna\CriticalCache\Interfaces\PauseServiceInterface;

interface PauseAfterUseContract
{

	/**
	 * @param PauseServiceInterface<T> $pauseService
	 *
	 * @template T
	 */
	public function execute(PauseServiceInterface $pauseService): void;

}
