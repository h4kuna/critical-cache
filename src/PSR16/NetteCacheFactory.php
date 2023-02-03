<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\PSR16;

use h4kuna\CriticalCache\PSR16CacheFactory;
use h4kuna\Dir\TempDir;
use Nette\Caching\Storage;
use Nette\Caching\Storages\FileStorage;

final class NetteCacheFactory implements PSR16CacheFactory
{

	public function __construct(private TempDir $tempDir)
	{
	}


	public function create(string $namespace): NetteCache
	{
		return new NetteCache($this->createStorage($namespace));
	}


	protected function createStorage(string $namespace): Storage
	{
		$storageDir = $this->tempDir;
		if ($namespace !== '') {
			$storageDir = $storageDir->dir($namespace);
		}

		return new FileStorage($storageDir->getDir());
	}

}
