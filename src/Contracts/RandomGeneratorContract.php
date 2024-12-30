<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Contracts;

interface RandomGeneratorContract
{
	/** @return non-empty-string */
	public function execute(): string;
}
