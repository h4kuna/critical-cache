<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Tests\Mock;

use h4kuna\CriticalCache\Lock\Notification\Listener;
use h4kuna\CriticalCache\Lock\Notification\Notifier;

/**
 * Records the calls, the listener is notified immediately.
 */
final class NotifierMock implements Notifier
{

	/**
	 * @var list<string>
	 */
	public array $calls = [];

	public function listen(string $channel): Listener
	{
		$this->calls[] = "listen $channel";

		return new class ($this) implements Listener {

			public function __construct(private NotifierMock $notifier)
			{
			}

			public function wait(float $timeout): bool
			{
				$this->notifier->calls[] = "wait $timeout";

				return true;
			}

		};
	}

	public function notify(string $channel): void
	{
		$this->calls[] = "notify $channel";
	}

}
