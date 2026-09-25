<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Lock\Notification\Redis;

use h4kuna\CriticalCache\Lock\Notification\Listener;
use Redis;
use function max;

final class RedisStreamListener implements Listener
{

	public function __construct(
		private Redis $redis,
		private string $key,
		private string $lastId,
	)
	{
	}

	public function wait(float $timeout): bool
	{
		$result = $this->redis->xread([$this->key => $this->lastId], 1, max(1, (int) ($timeout * 1000)));

		return $result !== false && $result !== [];
	}

}
