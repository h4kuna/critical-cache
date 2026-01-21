<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit\Caching;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use h4kuna\CriticalCache\Caching\ValidityAware\TimeRangeEncoder;
use h4kuna\CriticalCache\Caching\ValidityAwareCache;
use h4kuna\CriticalCache\Nette\Storage\MemoryTtlStorage;
use h4kuna\CriticalCache\Tests\Mock\ClockMutable;
use Nette\Bridges\Psr\PsrCacheAdapter;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../bootstrap.php';

final class ValidityAwareCacheTest extends TestCase
{
	public function testBasic(): void
	{
		$nowMutable = new \DateTime('2020-12-30 13:14:15');
		$clock = new ClockMutable($nowMutable);
		$service = new ValidityAwareCache(new PsrCacheAdapter(new MemoryTtlStorage($clock)), $clock, new TimeRangeEncoder($clock));

		Assert::null($service->get('foo')->value);

		$service->set('foo', 2, 1, 'lorem');
		Assert::same(self::date('2020-12-30 13:14:16'), self::date($service->get('foo')->from));
		Assert::equal(self::date('2020-12-30 13:14:18'), self::date($service->get('foo')->to));

		Assert::false($service->get('foo')->isValid(), 'before init');
		Assert::null($service->get('foo')->value());
		$nowMutable->modify('+1 second');

		Assert::true($service->get('foo')->isValid(), 'valid');
		Assert::same('lorem', $service->get('foo')->value());
		$nowMutable->modify('+3 second');

		Assert::false($service->get('foo')->isValid(), 'after expire');
		Assert::null($service->get('foo')->value());

		$service->set('foo', 1);
		Assert::true($service->get('foo')->isValid(), 'valid');
		Assert::null($service->get('foo')->from);
		Assert::equal(self::date('2020-12-30 13:14:20'), self::date($service->get('foo')->to));
		$nowMutable->modify('+2 second');
		Assert::false($service->get('foo')->isValid(), 'after expire');
	}

	private static function date(DateTimeImmutable|string|null $date): string
	{
		if ($date === null) {
			return '';
		}
		if (is_string($date)) {
			$date = new DateTimeImmutable($date);
		}
		$date->setTimezone(new DateTimeZone('UTC'));
		return $date->format(DateTimeInterface::RFC3339);
	}
}

(new ValidityAwareCacheTest())->run();
