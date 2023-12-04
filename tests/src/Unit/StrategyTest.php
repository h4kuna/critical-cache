<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit;

use h4kuna\CriticalCache\PSR16\NetteCache;
use h4kuna\CriticalCache\Strategy;
use h4kuna\CriticalCache\Utils\Dependency;
use Nette\Caching\Storages\FileStorage;
use Nette\Caching\Storages\MemoryStorage;
use Tester\Assert;
use Tester\Helpers;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class StrategyTest extends TestCase
{
	private const TempDir = __DIR__ . '/../../temp/strategy';


	public function testBasic(): void
	{
		$tempDir = self::tempDir('basic');
		$memoryCache = new NetteCache(new MemoryStorage());
		$fileCache = new NetteCache(new FileStorage($tempDir));

		$strategy = new Strategy(['memory' => $memoryCache, 'file' => $fileCache]);

		$data = $strategy->get('foo.test', function (Dependency $dependency): string {
			return 'empty';
		});

		Assert::same('empty', $data);
		Assert::same('empty', $strategy->get('foo.test'));
		Assert::same('empty', $memoryCache->get('foo.test'));
		Assert::same('empty', $fileCache->get('foo.test'));
	}


	public function testMemory(): void
	{
		$tempDir = self::tempDir('memory');
		$memoryCache = new NetteCache(new MemoryStorage()); // Memory implements CacheInterface
		$fileCache = new NetteCache(new FileStorage($tempDir)); // File implements CacheInterface
		$strategy = new Strategy(['memory' => $memoryCache, 'file' => $fileCache]); // add cache by priority

		$strategyFoo = $strategy->setStrategy($strategy::NoBreak, 'foo'); // use both cache in namespace foo
		$strategyBar = $strategyFoo->setStrategy('memory', 'bar'); // use memory cache in namespace foo.bar
		// $strategy->setStrategy('memory', 'bar'); // use memory cache in namespace bar

		$data = $strategyFoo->get('test', function (Dependency $dependency): string {
			return 'FOO';
		});

		$dataBar = $strategyBar->get('test', function (Dependency $dependency): string {
			return 'BAR';
		});

		Assert::same('FOO', $data);
		Assert::same('FOO', $strategyFoo->get('test'));
		Assert::same('FOO', $memoryCache->get('foo.test'));
		Assert::same('FOO', $fileCache->get('foo.test'));

		Assert::same('BAR', $dataBar);
		Assert::same('BAR', $strategyBar->get('test'));
		Assert::same('BAR', $memoryCache->get('foo.bar.test'));
		Assert::null($fileCache->get('foo.bar.test'));
	}


	private static function tempDir(string $dir): string
	{
		$tempDir = self::TempDir . '/' . $dir;
		Helpers::purge($tempDir);

		return $tempDir;
	}
}

(new StrategyTest())->run();
