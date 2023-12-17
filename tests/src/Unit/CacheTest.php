<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit;

use h4kuna\CriticalCache;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class CacheTest extends TestCase
{
	public function testBasic(): void
	{
		$criticalSection = self::createCache();

		Assert::null($criticalSection->get('foo'));
		Assert::true($criticalSection->delete('foo'));

		$criticalSection->set('foo', 'one', 5);

		Assert::same('one', $criticalSection->get('foo'));
		Assert::true($criticalSection->delete('foo'));
		Assert::null($criticalSection->get('foo'));
	}


	public function testTTL(): void
	{
		$criticalSection = self::createCache(__METHOD__);

		$criticalSection->set('foo', 'one', 1);
		sleep(2);
		Assert::null($criticalSection->get('foo'));
	}


	public function testMultiple(): void
	{
		$criticalSection = self::createCache(__METHOD__);

		$data = [];
		foreach ($criticalSection->getMultiple(['foo', 'bar']) as $key => $value) {
			$data[$key] = $value;
		}
		Assert::same(['foo' => null, 'bar' => null], $data);

		Assert::true($criticalSection->deleteMultiple(['foo', 'bar', 'baz']));

		$criticalSection->setMultiple(['foo' => 'one', 'bar' => 'two'], 5);

		$data = [];
		foreach ($criticalSection->getMultiple(['foo', 'bar', 'baz'], false) as $key => $value) {
			$data[$key] = $value;
		}
		Assert::same(['foo' => 'one', 'bar' => 'two', 'baz' => false], $data);

		Assert::true($criticalSection->deleteMultiple(['foo', 'bar', 'baz']));

		$data = [];
		foreach ($criticalSection->getMultiple(['foo', 'bar', 'baz'], false) as $key => $value) {
			$data[$key] = $value;
		}
		Assert::same(['foo' => false, 'bar' => false, 'baz' => false], $data);
	}


	public function testNamespace(): void
	{
		$criticalSection = self::createCache(__METHOD__);
		$criticalSection->set('foo', 'one', 5);
		Assert::same('one', $criticalSection->get('foo'));

		$newNamespace = $criticalSection->namespace('foo');
		Assert::null($newNamespace->get('foo'));

		Assert::true($newNamespace->delete('foo'));
		Assert::same('one', $criticalSection->get('foo'));

		$newNamespace->set('foo', 'two', 5);
		Assert::same('one', $criticalSection->get('foo'));
		Assert::same('two', $newNamespace->get('foo'));
		$newNamespace->delete('foo');
		Assert::null($newNamespace->get('foo'));
	}


	public function testClear(): void
	{
		$criticalSection = self::createCache(__METHOD__);
		$newNamespace = $criticalSection->namespace('foo');

		Assert::false($criticalSection->has('foo'));
		$criticalSection->set('foo', 'one', 5);
		Assert::true($criticalSection->has('foo'));
		$newNamespace->set('foo', 'two', 5);

		$newNamespace->clear();

		Assert::same('one', $criticalSection->get('foo'));
		Assert::null($newNamespace->get('foo'));
	}


	public function testLoad(): void
	{
		$criticalSection = self::createCache(__METHOD__);
		$count = 0;
		$value = $criticalSection->load('testfoo', function (CriticalCache\Utils\Dependency $dependency) use (&$count) {
			Assert::null($dependency->ttl);
			++$count;
			return 'bar';
		});
		Assert::same(1, $count);;
		Assert::same('bar', $value);
		$value = $criticalSection->load('testfoo', function (CriticalCache\Utils\Dependency $dependency) use (&$count) {
			Assert::null($dependency->ttl);
			++$count;
			return 'bar';
		});
		Assert::same(1, $count);;
		Assert::same('bar', $value);
	}


	private static function createCache(string $namespace = ''): CriticalCache\Cache
	{
		$cache = (new CriticalCache\CacheFactory(__DIR__ . '/../../temp'))->create();

		return $namespace === '' ? $cache : $cache->namespace($namespace);
	}

}

(new CacheTest())->run();
