<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\PSR16\Pool;

use Psr\SimpleCache\CacheInterface;

interface CachePoolFactoryInterface
{
	/**
	 * @param string|array<CacheInterface> $namespace
	 */
	public function create(string|array $namespace = ''): CacheInterface;
}
