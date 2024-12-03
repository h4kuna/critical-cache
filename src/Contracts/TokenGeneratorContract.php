<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Contracts;

interface TokenGeneratorContract
{
	public function generate(): string;
}
