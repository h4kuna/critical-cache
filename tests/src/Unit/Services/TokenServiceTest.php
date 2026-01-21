<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit\Services;

use h4kuna\CriticalCache\Services\RandomGenerator;
use h4kuna\CriticalCache\Services\TokenService;
use h4kuna\CriticalCache\Services\UseOneTimeService;
use h4kuna\CriticalCache\Tests\Mock\ValidServiceFactory;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../bootstrap.php';

final class TokenServiceTest extends TestCase
{
	public function testBasic(): void
	{
		$tokenService = new TokenService(new UseOneTimeService(ValidServiceFactory::create()), new RandomGenerator());
		Assert::null($tokenService->get('foo'));
		$token = $tokenService->make();
		Assert::true($tokenService->isEqual($token));
	}
}

(new TokenServiceTest())->run();
