<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit\Services;

use Closure;
use h4kuna\CriticalCache\Nette\Storage\MemoryTtlStorage;
use h4kuna\CriticalCache\Services\UseOneTimeService;
use Nette\Bridges\Psr\PsrCacheAdapter;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../bootstrap.php';

final class UseOneTimeServiceTest extends TestCase
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
		$service = new UseOneTimeService(new PsrCacheAdapter(new MemoryTtlStorage()));

		Assert::null($service->get('foo'));
		Assert::same('Lorem', $service->save('foo', 'Lorem', 2));
		Assert::same('Lorem', $service->get('foo'));
		Assert::null($service->get('foo'));
		Assert::null($service->get('bar'));

		Assert::same('Lorem', $service->save('foo', 'Lorem', 2));
		sleep(2);
		Assert::null($service->get('foo'));
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

(new UseOneTimeServiceTest())->run();
