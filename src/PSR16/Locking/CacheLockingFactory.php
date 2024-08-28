<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\PSR16\Locking;

use h4kuna\CriticalCache\Exceptions\MissingDependencyException;
use h4kuna\CriticalCache\Lock\LockOriginal;
use h4kuna\CriticalCache\Lock\Malkusch\CriticalSectionOriginal;
use h4kuna\CriticalCache\Nette\NetteCacheFactory;
use h4kuna\CriticalCache\PSR16\CacheLocking;
use h4kuna\CriticalCache\PSR16\CacheLockingFactoryInterface;
use h4kuna\CriticalCache\PSR16\PSR16CacheFactory;
use h4kuna\Dir\Dir;
use h4kuna\Dir\TempDir;

final class CacheLockingFactory implements CacheLockingFactoryInterface
{
	private PSR16CacheFactory $cacheFactory;
	private LockOriginal $lockOriginal;

	public function __construct(
		string|Dir|PSR16CacheFactory $cacheFactory,
		?LockOriginal $lockOriginal = null,
	) {
		if ($cacheFactory instanceof PSR16CacheFactory) {
			if ($lockOriginal === null) {
				throw new MissingDependencyException('$lockOriginal must be filled');
			}
			$this->cacheFactory = $cacheFactory;
			$this->lockOriginal = $lockOriginal;
		} else {
			$tempDir = self::createTempDir($cacheFactory);
			$this->cacheFactory = self::createPSR16CacheFactory($tempDir);
			$this->lockOriginal = self::createLockOriginal($tempDir);
		}
	}

	private static function createTempDir(string|Dir $dir): Dir
	{
		MissingDependencyException::checkH4kunaDir();

		return $dir instanceof Dir ? $dir : new TempDir($dir);
	}

	private static function createPSR16CacheFactory(Dir $dir): PSR16CacheFactory
	{
		MissingDependencyException::checkNetteCaching();

		return new NetteCacheFactory($dir->dir('h4kuna/cache'));
	}


	private static function createLockOriginal(Dir $dir): LockOriginal
	{
		MissingDependencyException::checkMalkuschLock();

		return new CriticalSectionOriginal($dir->dir('h4kuna/locks'));
	}

	public function create(string $namespace = ''): CacheLocking
	{
		return new CacheLock($this->cacheFactory->create($namespace), $this->lockOriginal);
	}

}
