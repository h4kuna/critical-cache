<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\PSR16;

use h4kuna\CriticalCache\PSR16CacheFactory;
use Nette\Caching\Storage;
use Nette\Caching\Storages\FileStorage;
use Nette\Utils\FileSystem;

final class NetteCacheFactory implements PSR16CacheFactory
{

	public function __construct(private string $tempDir)
	{
	}


	public function create(string $namespace): NetteCache
	{
		return new NetteCache($this->createStorage($namespace));
	}


	protected function createStorage(string $namespace): Storage
	{
		$storageDir = $this->tempDir . '/h4kuna/cache';
		if ($namespace !== '') {
			$storageDir .= "/$namespace";
		}
		FileSystem::createDir($storageDir);

		return new FileStorage($storageDir);
	}

}
