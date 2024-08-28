<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\PSR16;

interface CacheLockingFactoryInterface
{
	function create(): CacheLocking;
}
