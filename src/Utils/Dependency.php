<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Utils;

use DateInterval;

final class Dependency
{
	public DateInterval|int|null $ttl = null;

}
