<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit\PSR16\Locking;

use h4kuna\CriticalCache;
use h4kuna\CriticalCache\PSR16\CacheLocking;
use h4kuna\CriticalCache\PSR16\Locking\CacheLockingFactory;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class CacheLockTest extends TestCase
{
	public function testBasic(): void
	{
		$criticalSection = self::createCache(__FUNCTION__);

		Assert::null($criticalSection->get('foo'));
		Assert::true($criticalSection->delete('foo'));

		$criticalSection->set('foo', 'one', 5);

		Assert::same('one', $criticalSection->get('foo'));
		Assert::true($criticalSection->delete('foo'));
		Assert::null($criticalSection->get('foo'));
	}

	private static function createCache(string $namespace = ''): CacheLocking
	{
		if ($namespace !== '') {
			$namespace = '/' . str_replace(['\\', '::'], '_', $namespace);
		}

		$cache = (new CacheLockingFactory(__DIR__ . '/../../../../temp' . $namespace))->create();
		$cache->clear();

		return $cache;
	}

	public function testTTL(): void
	{
		$criticalSection = self::createCache(__FUNCTION__);

		$criticalSection->set('foo', 'one', 1);
		sleep(2);
		Assert::null($criticalSection->get('foo'));
	}

	public function testMultiple(): void
	{
		$criticalSection = self::createCache(__FUNCTION__);

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

	public function testClear(): void
	{
		$criticalSection = self::createCache(__FUNCTION__);

		Assert::false($criticalSection->has('foo'));
		$criticalSection->set('foo', 'one', 5);
		Assert::true($criticalSection->has('foo'));

		Assert::same('one', $criticalSection->get('foo'));
	}

	public function testLoad(): void
	{
		$criticalSection = self::createCache(__FUNCTION__);
		$count = 0;
		$value = $criticalSection->load('testfoo', function (CriticalCache\Utils\Dependency $dependency) use (&$count) {
			Assert::null($dependency->ttl);
			++$count;

			return 'bar';
		});
		Assert::same(1, $count);
		Assert::same('bar', $value);
		$value = $criticalSection->load('testfoo', function (CriticalCache\Utils\Dependency $dependency) use (&$count) {
			Assert::null($dependency->ttl);
			++$count;

			return 'bar';
		});
		Assert::same(1, $count);;
		Assert::same('bar', $value);
	}

	public function testLoadNamespace(): void
	{
		$criticalSection = self::createCache(__FUNCTION__);
		$count = 0;
		$value = $criticalSection->load('testfoo', function (
			CriticalCache\Utils\Dependency $dependency,
			$cache,
			$prefix,
		) use (&$count) {
			Assert::null($dependency->ttl);
			Assert::same('testfoo', $prefix);
			++$count;

			return 'bar';
		});
		Assert::same(1, $count);
		Assert::same('bar', $value);
	}

}

(new CacheLockTest())->run();
