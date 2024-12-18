<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Contracts;

use h4kuna\CriticalCache\Interfaces\PauseServiceInterface;

interface PauseAfterUseContract
{
	public function execute(PauseServiceInterface $pauseService): void;
}
