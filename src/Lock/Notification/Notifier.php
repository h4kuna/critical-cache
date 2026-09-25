<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Lock\Notification;

/**
 * Wakes up all processes waiting for the critical section at once, instead of letting them take the lock one by one.
 */
interface Notifier
{

	/**
	 * Start listening before trying the lock, the notification sent meanwhile is not lost.
	 */
	public function listen(string $channel): Listener;

	public function notify(string $channel): void;

}
