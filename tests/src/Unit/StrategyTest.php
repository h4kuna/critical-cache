<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit;

use h4kuna\CriticalCache\PSR16\NetteCache;
use h4kuna\CriticalCache\Strategy;
use h4kuna\CriticalCache\Utils\Dependency;
use Nette\Caching\Storages\FileStorage;
use Nette\Caching\Storages\MemoryStorage;
use Nette\Utils\FileSystem;
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
		Assert::same(['data' => 'empty', 'ttl' => null], $memoryCache->get('foo.test'));
		Assert::same(['data' => 'empty', 'ttl' => null], $fileCache->get('foo.test'));
	}


	public function testDeleteClear(): void
	{
		$tempDir = self::tempDir('delete-clear');
		$memoryCache = new NetteCache(new MemoryStorage()); // Memory implements CacheInterface
		$fileCache = new NetteCache(new FileStorage($tempDir)); // File implements CacheInterface
		$strategy = new Strategy(['memory' => $memoryCache, 'file' => $fileCache]); // add cache by priority

		$strategy->set('a', 'a');
		$strategy->set('b', 'b');
		$strategy->set('c', 'c');
		$strategy->delete('c');
		Assert::same('a', $strategy->get('a'));
		Assert::same('b', $strategy->get('b'));
		Assert::null($strategy->get('c'));

		$strategy->clear();
		Assert::null($strategy->get('a'));
		Assert::null($strategy->get('b'));
		Assert::null($strategy->get('c'));
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
		Assert::same(['data' => 'FOO', 'ttl' => null], $memoryCache->get('foo.test'));
		Assert::same(['data' => 'FOO', 'ttl' => null], $fileCache->get('foo.test'));

		Assert::same('BAR', $dataBar);
		Assert::same('BAR', $strategyBar->get('test'));
		Assert::same(['data' => 'BAR', 'ttl' => null], $memoryCache->get('foo.bar.test'));
		Assert::null($fileCache->get('foo.bar.test'));
	}


	public function testUpdateParent(): void
	{
		$tempDir = self::tempDir('memory-parent');
		$memoryCache = new NetteCache(new MemoryStorage()); // Memory implements CacheInterface
		$fileCache = new NetteCache(new FileStorage($tempDir)); // File implements CacheInterface
		$strategy = new Strategy(['memory' => $memoryCache, 'file' => $fileCache]); // add cache by priority

		$expire = time() + 80;
		$fileCache->set('test', ['data' => 'a', 'ttl' => $expire], 80);
		Assert::same('a', $strategy->get('test'));
		Assert::same(['data' => 'a', 'ttl' => $expire], $memoryCache->get('test'));
	}


	private static function tempDir(string $dir): string
	{
		$tempDir = self::TempDir . '/' . $dir;
		FileSystem::createDir($tempDir);
		Helpers::purge($tempDir);

		return $tempDir;
	}
}

(new StrategyTest())->run();
