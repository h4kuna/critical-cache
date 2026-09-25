<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Tests\Unit\Lock;

use h4kuna\CriticalCache\Lock\Notification\Redis\RedisStreamNotifier;
use Redis;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;
use function extension_loaded;
use function getenv;
use function microtime;
use function uniqid;
use function usleep;

require __DIR__ . '/../../bootstrap.php';

final class RedisStreamNotifierTest extends TestCase
{

	private RedisStreamNotifier $notifier;

	protected function setUp(): void
	{
		if (extension_loaded('redis') === false || getenv('REDIS_HOST') === false) {
			Environment::skip('Needs ext-redis and REDIS_HOST.');
		}

		$redis = new Redis();
		$redis->connect((string) getenv('REDIS_HOST'));
		$this->notifier = new RedisStreamNotifier($redis, uniqid('test:', true) . ':');
	}

	public function testNotificationSentBeforeWaitIsNotLost(): void
	{
		$listener = $this->notifier->listen('a');
		$this->notifier->notify('a');

		$start = microtime(true);
		Assert::true($listener->wait(1.0));
		Assert::true(microtime(true) - $start < 0.5);
	}

	public function testNotificationBeforeListenIsIgnored(): void
	{
		$this->notifier->notify('a');
		// a notification from the same millisecond wakes the listener up, better an extra wake-up than a lost one
		usleep(2_000);
		$listener = $this->notifier->listen('a');

		Assert::false($listener->wait(0.1));
	}

	public function testOtherChannel(): void
	{
		$listener = $this->notifier->listen('a');
		$this->notifier->notify('b');

		Assert::false($listener->wait(0.1));
	}

}

(new RedisStreamNotifierTest())->run();
