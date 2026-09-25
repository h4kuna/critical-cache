<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Tests\Integration\Stampede;

use h4kuna\Dir\TempDir;
use RuntimeException;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;
use function array_column;
use function array_unique;
use function escapeshellarg;
use function extension_loaded;
use function file;
use function getenv;
use function json_decode;
use function max;
use function min;
use function proc_close;
use function proc_open;
use function stream_get_contents;
use function uniqid;
use const FILE_IGNORE_NEW_LINES;
use const PHP_BINARY;

require __DIR__ . '/../../bootstrap.php';

/**
 * Six processes want the same missing key at once, only one of them builds the value.
 *
 * @testCase
 */
final class StampedeTest extends TestCase
{

	private const PROCESSES = 6;

	public function testFlock(): void
	{
		$results = self::stampede('flock');

		self::assertOneBuild($results);
	}

	public function testRedisNotification(): void
	{
		if (extension_loaded('redis') === false || getenv('REDIS_HOST') === false) {
			Environment::skip('Needs ext-redis and REDIS_HOST.');
		}

		$results = self::stampede('redis');

		self::assertOneBuild($results);
		// all the waiting processes wake up at once, not one by one after polling the lock
		/** @var non-empty-list<float> $ends */
		$ends = array_column($results['workers'], 'end');
		Assert::true(max($ends) - min($ends) < 0.05);
	}

	/**
	 * @return array{workers: list<array{value: string, end: float}>, builds: list<string>}
	 */
	private static function stampede(string $mode): array
	{
		$path = (new TempDir(uniqid('stampede-', true)))->getDir();
		$key = uniqid('key-', true);

		$processes = [];
		for ($i = 0; $i < self::PROCESSES; ++$i) {
			$command = PHP_BINARY . ' ' . escapeshellarg(__DIR__ . '/worker.php') . ' ' . escapeshellarg($mode) . ' ' . escapeshellarg($path) . ' ' . escapeshellarg($key);
			$process = proc_open($command, [1 => ['pipe', 'w']], $pipes);
			if ($process === false) {
				throw new RuntimeException("Could not run: $command");
			}
			$processes[] = [$process, $pipes[1]];
		}

		$workers = [];
		foreach ($processes as [$process, $stdout]) {
			/** @var array{value: string, end: float} $worker */
			$worker = json_decode((string) stream_get_contents($stdout), true);
			$workers[] = $worker;
			proc_close($process);
		}

		$builds = file("$path/builds", FILE_IGNORE_NEW_LINES);

		return ['workers' => $workers, 'builds' => $builds === false ? [] : $builds];
	}

	/**
	 * @param array{workers: list<array{value: string, end: float}>, builds: list<string>} $results
	 */
	private static function assertOneBuild(array $results): void
	{
		Assert::count(1, $results['builds']);
		Assert::count(self::PROCESSES, $results['workers']);
		Assert::same(['value-' . $results['builds'][0]], array_unique(array_column($results['workers'], 'value')));
	}

}

(new StampedeTest())->run();
