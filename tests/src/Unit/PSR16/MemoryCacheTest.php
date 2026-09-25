<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Tests\Unit\PSR16;

use DateInterval;
use h4kuna\CriticalCache\PSR16\MemoryCache;
use h4kuna\CriticalCache\Tests\Mock\ClockMutable;
use Tester\Assert;
use Tester\TestCase;
use function iterator_to_array;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class MemoryCacheTest extends TestCase
{

	public function testBasic(): void
	{
		$cache = new MemoryCache();

		Assert::false($cache->has('foo'));
		Assert::null($cache->get('foo'));
		Assert::same('default', $cache->get('foo', 'default'));

		Assert::true($cache->set('foo', 'bar'));
		Assert::true($cache->has('foo'));
		Assert::same('bar', $cache->get('foo'));

		Assert::true($cache->set('foo', null));
		Assert::true($cache->has('foo'));
		Assert::same('default', $cache->get('foo', 'default'));

		Assert::true($cache->delete('foo'));
		Assert::false($cache->has('foo'));

		Assert::true($cache->setMultiple(['a' => 1, 'b' => 2]));
		Assert::same(['a' => 1, 'b' => 2, 'c' => null], iterator_to_array($cache->getMultiple(['a', 'b', 'c'])));
		Assert::true($cache->deleteMultiple(['a']));
		Assert::same(['a' => null, 'b' => 2], iterator_to_array($cache->getMultiple(['a', 'b'])));

		Assert::true($cache->clear());
		Assert::false($cache->has('b'));
	}

	public function testTtl(): void
	{
		$clock = new ClockMutable();
		$cache = new MemoryCache($clock);

		$cache->set('int', 'a', 10);
		$cache->set('interval', 'b', new DateInterval('PT20S'));
		$cache->set('forever', 'c');

		$clock->dataTime->modify('+10 seconds');
		Assert::same('a', $cache->get('int'));
		Assert::same('b', $cache->get('interval'));

		$clock->dataTime->modify('+1 second');
		Assert::false($cache->has('int'));
		Assert::null($cache->get('int'));
		Assert::same('b', $cache->get('interval'));

		$clock->dataTime->modify('+10 seconds');
		Assert::null($cache->get('interval'));
		Assert::same('c', $cache->get('forever'));
	}

}

(new MemoryCacheTest())->run();
