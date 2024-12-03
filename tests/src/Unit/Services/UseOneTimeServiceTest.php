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
	public function testBasic(): void
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
}

(new UseOneTimeServiceTest())->run();
