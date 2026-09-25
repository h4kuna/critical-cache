<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Tests\Unit\Lock;

use h4kuna\CriticalCache\Lock\Symfony\SymfonyCriticalSection;
use h4kuna\CriticalCache\Tests\Mock\NotifierMock;
use h4kuna\Dir\TempDir;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use Tester\Assert;
use Tester\TestCase;
use function uniqid;

require __DIR__ . '/../../bootstrap.php';

final class SymfonyCriticalSectionTest extends TestCase
{

	public function testSynchronized(): void
	{
		$lockFactory = self::createLockFactory();
		$criticalSection = new SymfonyCriticalSection($lockFactory);

		Assert::same('foo', $criticalSection->synchronized('a', static fn (): string => 'foo'));
		// the lock is released
		Assert::true($lockFactory->createLock('a')->acquire(false));
	}

	public function testSingleFlightRunsCallbackAndNotifies(): void
	{
		$notifier = new NotifierMock();
		$criticalSection = new SymfonyCriticalSection(self::createLockFactory(), $notifier);

		$value = $criticalSection->singleFlight('a', static fn (): string => 'callback', static fn (): string => 'after wait');

		Assert::same('callback', $value);
		Assert::same(['listen a', 'notify a'], $notifier->calls);
	}

	public function testSingleFlightWaitsForTheOtherProcess(): void
	{
		$lockFactory = self::createLockFactory();
		$notifier = new NotifierMock();
		$criticalSection = new SymfonyCriticalSection($lockFactory, $notifier, waitTimeout: 0.5);

		// another process holds the lock, FlockStore opens a new file handle for every lock
		$lock = $lockFactory->createLock('a');
		Assert::true($lock->acquire(false));

		$value = $criticalSection->singleFlight('a', static fn (): string => 'callback', static fn (): string => 'after wait');

		Assert::same('after wait', $value);
		Assert::same(['listen a', 'wait 0.5'], $notifier->calls);
		$lock->release();
	}

	private static function createLockFactory(): LockFactory
	{
		return new LockFactory(new FlockStore((new TempDir(uniqid('locks-', true)))->getDir()));
	}

}

(new SymfonyCriticalSectionTest())->run();
