<?php declare(strict_types=1);

namespace h4kuna\CriticalCache;

use h4kuna\CriticalCache\PSR16\NetteCacheFactory;
use h4kuna\CriticalCache\Lock\CriticalSectionOriginal;
use League\Flysystem;
use Nette\Utils;

class CacheFactory
{

	public function __construct(protected string $tempDir = '')
	{
		if ($this->tempDir === '') {
			$this->tempDir = sys_get_temp_dir();
		}
	}


	public function create(): Cache
	{
		return new Cache($this->createPSR16CacheFactory(), $this->createLockOriginal());
	}


	protected function createPSR16CacheFactory(): PSR16CacheFactory
	{
		Dependency::checkNetteCaching();
		Dependency::checkNetteUtils();

		return new NetteCacheFactory($this->tempDir);
	}


	protected function createLockOriginal(): LockOriginal
	{
		return new CriticalSectionOriginal($this->createFileSystemOperator());
	}


	protected function createFileSystemOperator(): Flysystem\FilesystemOperator
	{
		Dependency::checkLeagueFileSystem();

		return new Flysystem\Filesystem($this->createFileSystemAdapter());
	}


	protected function createFileSystemAdapter(): Flysystem\FilesystemAdapter
	{
		Dependency::checkNetteUtils();
		$lockDir = $this->tempDir . '/h4kuna/locks';
		Utils\FileSystem::createDir($lockDir);

		return new Flysystem\Local\LocalFilesystemAdapter($lockDir);
	}

}
