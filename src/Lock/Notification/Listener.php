<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Lock\Notification;

interface Listener
{

	/**
	 * @return bool false on timeout
	 */
	public function wait(float $timeout): bool;

}
