<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Nette;

use h4kuna\CriticalCache\PSR16\PSR16CacheFactory;
use h4kuna\Dir\Dir;
use Nette\Bridges\Psr\PsrCacheAdapter;
use Nette\Caching\Storage;
use Nette\Caching\Storages\FileStorage;

final class NetteCacheFactory implements PSR16CacheFactory
{

	public function __construct(private Dir $tempDir)
	{
	}


	public function create(string $namespace = ''): PsrCacheAdapter
	{
		return new PsrCacheAdapter($this->createStorage($namespace));
	}


	private function createStorage(string $namespace): Storage
	{
		$storageDir = $this->tempDir;
		if ($namespace !== '') {
			$storageDir = $storageDir->dir($namespace);
		}

		return new FileStorage($storageDir->getDir());
	}

}
