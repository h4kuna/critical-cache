<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\PSR16\Locking;

use h4kuna\CriticalCache\Exceptions\MissingDependencyException;
use h4kuna\CriticalCache\Lock\CriticalSection;
use h4kuna\CriticalCache\Lock\Symfony\SymfonyCriticalSection;
use h4kuna\CriticalCache\Nette\NetteCacheFactory;
use h4kuna\CriticalCache\PSR16\CacheLocking;
use h4kuna\CriticalCache\PSR16\CacheLockingFactoryInterface;
use h4kuna\CriticalCache\PSR16\PSR16CacheFactory;
use h4kuna\Dir\Dir;
use h4kuna\Dir\TempDir;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

final class CacheLockingFactory implements CacheLockingFactoryInterface
{

	private PSR16CacheFactory $cacheFactory;

	private CriticalSection $criticalSection;

	public function __construct(
		string|Dir|PSR16CacheFactory $cacheFactory,
		?CriticalSection $criticalSection = null,
	)
	{
		if ($cacheFactory instanceof PSR16CacheFactory) {
			if ($criticalSection === null) {
				throw new MissingDependencyException('$criticalSection must be filled');
			}
			$this->cacheFactory = $cacheFactory;
			$this->criticalSection = $criticalSection;
		} else {
			$tempDir = self::createTempDir($cacheFactory);
			$this->cacheFactory = self::createPSR16CacheFactory($tempDir);
			$this->criticalSection = $criticalSection ?? self::createCriticalSection($tempDir);
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

	private static function createCriticalSection(Dir $dir): CriticalSection
	{
		MissingDependencyException::checkSymfonyLock();

		return new SymfonyCriticalSection(new LockFactory(new FlockStore($dir->dir('h4kuna/locks')->getDir())));
	}

	public function create(string $namespace = ''): CacheLocking
	{
		return new CacheLock($this->cacheFactory->create($namespace), $this->criticalSection);
	}

}
