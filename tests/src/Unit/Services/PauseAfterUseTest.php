<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit\Services;

use Beste\Clock\SystemClock;
use h4kuna\CriticalCache\Nette\Storage\MemoryTtlStorage;
use h4kuna\CriticalCache\PSR16\Locking\CacheLock;
use h4kuna\CriticalCache\Services\PauseAfterUse;
use h4kuna\CriticalCache\Services\PauseService;
use h4kuna\CriticalCache\Tests\Mock\LockOriginalMock;
use h4kuna\DataType\Date\Time;
use Nette\Bridges\Psr\PsrCacheAdapter;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../bootstrap.php';

final class PauseAfterUseTest extends TestCase
{
	public function testExecute(): void
	{
		$cacheLock = new CacheLock(new PsrCacheAdapter(new MemoryTtlStorage()), new LockOriginalMock());
		$service = new PauseAfterUse($cacheLock);
		$pauseService = new class (SystemClock::create(), 3) extends PauseService {
			protected function run(): void
			{
			}
		};

		$start = Time::micro();
		$service->execute($pauseService);
		$service->execute($pauseService);
		Assert::same(3.0, round(Time::micro() - $start));
	}
}

(new PauseAfterUseTest())->run();
