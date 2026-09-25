<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Tests\Integration\Stampede;

use h4kuna\CriticalCache\Lock\Notification\Redis\RedisStreamNotifier;
use h4kuna\CriticalCache\Lock\Symfony\SymfonyCriticalSection;
use h4kuna\CriticalCache\Nette\NetteCacheFactory;
use h4kuna\CriticalCache\PSR16\Locking\CacheLock;
use h4kuna\Dir\Dir;
use Redis;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\Store\RedisStore;
use function file_put_contents;
use function getenv;
use function getmypid;
use function json_encode;
use function microtime;
use function usleep;
use const FILE_APPEND;
use const LOCK_EX;

require __DIR__ . '/../../../../vendor/autoload.php';

/**
 * One process of the stampede, prints the loaded value and when it got it.
 *
 * usage: php worker.php <flock|redis> <dir> <key>
 */
[, $mode, $path, $key] = $argv;
$dir = new Dir($path);

if ($mode === 'redis') {
	$redis = new Redis();
	$redis->connect((string) getenv('REDIS_HOST'));
	$criticalSection = new SymfonyCriticalSection(new LockFactory(new RedisStore($redis)), new RedisStreamNotifier($redis));
} else {
	$criticalSection = new SymfonyCriticalSection(new LockFactory(new FlockStore($dir->dir('locks')->getDir())));
}

$cache = new CacheLock((new NetteCacheFactory($dir->dir('cache')))->create(), $criticalSection);

$value = $cache->load($key, static function () use ($path): string {
	file_put_contents("$path/builds", getmypid() . "\n", FILE_APPEND | LOCK_EX);
	usleep(500_000);

	return 'value-' . getmypid();
});

echo json_encode(['value' => $value, 'end' => microtime(true)]);
