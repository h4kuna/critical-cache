<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Lock;

use h4kuna\Memoize\Memoize;


abstract class LockOriginalAbstract implements LockOriginal
{
	use Memoize;

	public function get(string $name): Lock
	{
		return $this->memoize([__METHOD__, $name], function () use ($name): Lock {
			return $this->createLock($name);
		});
	}

	abstract protected function createLock(string $name): Lock;

}
