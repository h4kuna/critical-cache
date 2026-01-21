<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit\Services;

use h4kuna\CriticalCache\Tests\Mock\ValidServiceFactory;
use Tester\Assert;
use Tester\TestCase;
use h4kuna\CriticalCache\Services\UseOneTimeService;

require_once __DIR__ . '/../../bootstrap.php';

final class UseOneTimeServiceTest extends TestCase
{
	public function testBasic(): void
	{
		$service = new UseOneTimeService(ValidServiceFactory::create());

		Assert::null($service->get('foo'));
		Assert::same('Lorem', $service->set('foo', 'Lorem', 2));
		Assert::same('Lorem', $service->get('foo'));
		Assert::null($service->get('foo'));
		Assert::null($service->get('bar'));

		Assert::same('Lorem', $service->set('foo', 'Lorem', 2));
		sleep(2);
		Assert::null($service->get('foo'));
	}

	public function testValidFrom(): void
	{
		$service = new UseOneTimeService(ValidServiceFactory::create());

		Assert::null($service->get('foo'));
		Assert::same('Lorem', $service->set('foo', 'Lorem', 4, new \DateTimeImmutable('+2 seconds')));
		Assert::null($service->get('foo'));
		sleep(2);
		Assert::same('Lorem', $service->get('foo'));
		sleep(2);
		Assert::null($service->get('foo'));

		Assert::same('Lorem', $service->set('foo', 'Lorem', 1, new \DateTimeImmutable('+1 seconds')));
		sleep(2);
		Assert::null($service->get('foo'));
	}
}

(new UseOneTimeServiceTest())->run();
