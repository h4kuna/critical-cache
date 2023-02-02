<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Lock;

use h4kuna\CriticalCache\Lock;
use h4kuna\CriticalCache\LockOriginal;
use h4kuna\Memoize\MemoryStorage;
use League\Flysystem\FilesystemOperator;
use malkusch\lock\mutex\FlockMutex;

final class CriticalSectionOriginal implements LockOriginal
{
	use MemoryStorage;

	public function __construct(private FilesystemOperator $tempDir)
	{
	}


	public function get(string $name): Lock
	{
		return $this->memoize([__METHOD__, $name], function () use ($name) {
			$filename = md5($name) . '.lock';
			$this->tempDir->write($filename, '');
			return new CriticalSection(
				new FlockMutex(
					$this->tempDir->readStream($filename)
				)
			);
		});
	}

}
