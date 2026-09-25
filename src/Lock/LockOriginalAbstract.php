<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Lock;

abstract class LockOriginalAbstract implements LockOriginal
{

	/**
	 * @var array<string, Lock>
	 */
	private array $locks = [];

	public function get(string $name): Lock
	{
		return $this->locks[$name] ??= $this->createLock($name);
	}

	abstract protected function createLock(string $name): Lock;

}
