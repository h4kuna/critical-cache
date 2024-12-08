<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Contracts;

interface RandomGeneratorContract
{
	public function execute(): string;
}
