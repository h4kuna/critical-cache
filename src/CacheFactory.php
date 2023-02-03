<?php declare(strict_types=1);

namespace h4kuna\CriticalCache;

use h4kuna\CriticalCache\PSR16\NetteCacheFactory;
use h4kuna\CriticalCache\Lock\CriticalSectionOriginal;
use h4kuna\Dir\TempDir;

class CacheFactory
{
	protected TempDir $tempDir;


	public function __construct(string $tempDir = '')
	{
		if ($tempDir === '') {
			$tempDir = sys_get_temp_dir();
		}

		Dependency::checkH4kunaDir();
		$this->tempDir = new TempDir($tempDir);
	}


	public function create(): Cache
	{
		return new Cache($this->createPSR16CacheFactory(), $this->createLockOriginal());
	}


	protected function createPSR16CacheFactory(): PSR16CacheFactory
	{
		Dependency::checkNetteCaching();

		return new NetteCacheFactory($this->tempDir->dir('h4kuna/cache'));
	}


	protected function createLockOriginal(): LockOriginal
	{
		return new CriticalSectionOriginal($this->tempDir->dir('h4kuna/locks'));
	}

}
