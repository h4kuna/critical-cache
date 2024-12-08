<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit\Services;

use h4kuna\CriticalCache\Nette\Storage\MemoryTtlStorage;
use h4kuna\CriticalCache\Services\RandomGenerator;
use h4kuna\CriticalCache\Services\TokenService;
use h4kuna\CriticalCache\Services\UseOneTimeService;
use Nette\Bridges\Psr\PsrCacheAdapter;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../bootstrap.php';

final class TokenServiceTest extends TestCase
{
	public function testBasic(): void
	{
		$tokenService = new TokenService(new UseOneTimeService(new PsrCacheAdapter(new MemoryTtlStorage())), new RandomGenerator());
		Assert::null($tokenService->get('foo'));
		$token = $tokenService->make();
		Assert::true($tokenService->compare($token));
	}
}

(new TokenServiceTest())->run();
