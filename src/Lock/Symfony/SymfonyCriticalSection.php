<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Lock\Symfony;

use Closure;
use h4kuna\CriticalCache\Lock\CriticalSection;
use h4kuna\CriticalCache\Lock\Notification\Notifier;
use Symfony\Component\Lock\LockFactory;

/**
 * Without the notifier the waiting processes take the lock one by one after it is released. Blocking stores
 * (FlockStore, PostgreSqlStore, ...) wake them up, the others (RedisStore, ...) poll.
 * With the notifier all the waiting processes wake up at once.
 */
final class SymfonyCriticalSection implements CriticalSection
{

	/**
	 * @param float $ttl lifetime of the lock for expiring stores (Redis, Memcached, ...), a crashed process does not block the others forever
	 * @param float $waitTimeout how long to wait for the notification, then the process falls back to the lock
	 */
	public function __construct(
		private LockFactory $lockFactory,
		private ?Notifier $notifier = null,
		private float $ttl = 300.0,
		private float $waitTimeout = 5.0,
	)
	{
	}

	public function synchronized(
		string $name,
		Closure $callback,
	): mixed
	{
		$lock = $this->lockFactory->createLock($name, $this->ttl);
		$lock->acquire(true);
		try {
			return $callback();
		} finally {
			$lock->release();
		}
	}

	public function singleFlight(
		string $name,
		Closure $callback,
		Closure $afterWait,
	): mixed
	{
		$listener = $this->notifier?->listen($name);
		$lock = $this->lockFactory->createLock($name, $this->ttl);
		if ($lock->acquire(false)) {
			try {
				return $callback();
			} finally {
				$lock->release();
				// after release, so every process which failed to take the lock is already listening
				$this->notifier?->notify($name);
			}
		}

		if ($listener === null) {
			$lock->acquire(true);
			$lock->release();
		} else {
			$listener->wait($this->waitTimeout);
		}

		return $afterWait();
	}

}
