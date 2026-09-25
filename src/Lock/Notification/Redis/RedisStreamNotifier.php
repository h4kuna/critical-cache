<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Lock\Notification\Redis;

use h4kuna\CriticalCache\Lock\Notification\Listener;
use h4kuna\CriticalCache\Lock\Notification\Notifier;
use Redis;
use function assert;
use function intdiv;
use function is_array;
use function sprintf;

/**
 * Redis Streams keep the notification, unlike Pub/Sub, so a listener started before the notification always gets it.
 * The stream expires, a notification is only a signal, the data are in the cache.
 */
final class RedisStreamNotifier implements Notifier
{

	public function __construct(
		private Redis $redis,
		private string $prefix = 'critical-cache:notify:',
		private int $expire = 60,
	)
	{
	}

	public function listen(string $channel): Listener
	{
		$time = $this->redis->time();
		assert(is_array($time));
		// phpredis returns strings, its stub says int
		$milliseconds = (int) $time[0] * 1000 + intdiv((int) $time[1], 1000); // @phpstan-ignore cast.useless, cast.useless

		// the last possible id of the previous millisecond, every entry added from now on has a greater id
		return new RedisStreamListener($this->redis, $this->prefix . $channel, sprintf('%d-18446744073709551615', $milliseconds - 1));
	}

	public function notify(string $channel): void
	{
		$key = $this->prefix . $channel;
		$this->redis->xadd($key, '*', ['n' => '1'], 10, true);
		$this->redis->expire($key, $this->expire);
	}

}
