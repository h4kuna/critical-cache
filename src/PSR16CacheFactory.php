<?php declare(strict_types=1);

namespace h4kuna\CriticalCache;

use Psr\SimpleCache\CacheInterface;

interface PSR16CacheFactory
{

	function create(string $namespace): CacheInterface;

}
