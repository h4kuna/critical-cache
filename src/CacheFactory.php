<?php declare(strict_types=1);

namespace h4kuna\CriticalCache;

use h4kuna\CriticalCache\Exceptions\MissingDependencyException;
use h4kuna\CriticalCache\PSR16\NetteCacheFactory;
use h4kuna\CriticalCache\Lock\CriticalSectionOriginal;
use h4kuna\Dir\TempDir;

class CacheFactory
{

	public function __construct(protected string $tempDir = '')
	{
	}


	public function create(): Cache
	{
		return new Cache($this->createPSR16CacheFactory(), $this->createLockOriginal());
	}


	protected function createPSR16CacheFactory(): PSR16CacheFactory
	{
		MissingDependencyException::checkNetteCaching();

		return new NetteCacheFactory($this->createTempDir()->dir('h4kuna/cache'));
	}


	protected function createLockOriginal(): LockOriginal
	{
		MissingDependencyException::checkMalkuschLock();

		return new CriticalSectionOriginal($this->createTempDir()->dir('h4kuna/locks'));
	}


	protected function createTempDir(): TempDir
	{
		MissingDependencyException::checkH4kunaDir();

		return new TempDir($this->tempDir);
	}

}