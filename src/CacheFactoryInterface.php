<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache;

interface CacheFactoryInterface
{
	function create(): CacheLocking;
}
