<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Interfaces;

interface RandomGeneratorInterface
{
	/** @return non-empty-string */
	public function execute(): string;
}
