<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Lock;

use h4kuna\CriticalCache\Exceptions\OpenFileFailedException;
use h4kuna\CriticalCache\Lock;
use h4kuna\CriticalCache\LockOriginal;
use h4kuna\Dir\Dir;
use h4kuna\Dir\TempDir;
use h4kuna\Memoize\MemoryStorage;
use malkusch\lock\mutex\FlockMutex;

final class CriticalSectionOriginal implements LockOriginal
{
	use MemoryStorage;

	public function __construct(private Dir $tempDir)
	{
	}


	public function get(string $name): Lock
	{
		return $this->memoize([__METHOD__, $name], function () use ($name) {
			$filename = $this->tempDir->filename(md5($name), 'lock');
			if (touch($filename) === false || ($resource = fopen($filename, 'r')) === false) {
				throw new OpenFileFailedException($filename);
			}

			return new CriticalSection(
				new FlockMutex($resource)
			);
		});
	}

}
