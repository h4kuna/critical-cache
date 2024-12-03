<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit\Services;

use Closure;
use h4kuna\CriticalCache\Nette\Storage\MemoryTtlStorage;
use h4kuna\CriticalCache\Services\ValidService;
use h4kuna\CriticalCache\Tests\ClockTest;
use Nette\Bridges\Psr\PsrCacheAdapter;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../bootstrap.php';

final class ValidServiceTest extends TestCase
{
	/**
	 * @return array<string|int, array{0: Closure(static):void}>
	 */
	public static function data(): array
	{
		return [
			[
				static function (self $self) {
					$self->assert();
				},
			],
		];
	}

	public function assert(): void
	{
		$service = new ValidService(new PsrCacheAdapter(new MemoryTtlStorage()), new ClockTest(0));

		Assert::null($service->from('foo'));
		Assert::null($service->to('foo'));
		Assert::false($service->isValid('foo'));

		$service->set('foo', 2, 1, 'lorem');
		$now = new \DateTimeImmutable();

		Assert::false($service->isValid('foo'), 'before init');
		Assert::null($service->value('foo'));
		sleep(1);
		Assert::true($service->isValid('foo'), 'valid');
		Assert::same('lorem', $service->value('foo'));
		Assert::same($service->to('foo')?->format(\DateTimeInterface::RFC3339), $now->modify('+2 second')->format(\DateTimeInterface::RFC3339));
		Assert::same($service->from('foo')?->format(\DateTimeInterface::RFC3339), $now->modify('+1 second')->format(\DateTimeInterface::RFC3339));
		sleep(1);
		Assert::false($service->isValid('foo'), 'after expire');
		Assert::null($service->value('foo'));

		$service->set('foo', 1);
		$now = new \DateTimeImmutable();
		Assert::true($service->isValid('foo'), 'valid');
		Assert::null($service->from('foo'));
		Assert::same($service->to('foo')?->format(\DateTimeInterface::RFC3339), $now->modify('+1 second')->format(\DateTimeInterface::RFC3339));
		sleep(1);
		Assert::false($service->isValid('foo'), 'after expire');
	}

	/**
	 * @param Closure(static):void $assert
	 * @dataProvider data
	 */
	public function testBasic(Closure $assert): void
	{
		$assert($this);
	}
}

(new ValidServiceTest())->run();
