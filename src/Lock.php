<?php declare(strict_types=1);

namespace h4kuna\CriticalCache;

interface Lock
{
	/**
	 * @template T
	 * @param callable(): T $code
	 * @return T
	 */
	function synchronized(callable $code);

}
