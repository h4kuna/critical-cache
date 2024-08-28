<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Lock\Malkusch;

use h4kuna\CriticalCache\Exceptions\OpenFileFailedException;
use h4kuna\CriticalCache\Lock\Lock;
use h4kuna\CriticalCache\Lock\LockOriginalAbstract;
use h4kuna\Dir\Dir;
use malkusch\lock\mutex\FlockMutex;

/**
 * This implementation support FlockMutex from package malkusch/lock
 */
final class CriticalSectionOriginal extends LockOriginalAbstract
{
	public function __construct(private Dir $tempDir)
	{
	}

	protected function createLock(string $name): Lock
	{
		$filename = $this->tempDir->filename(md5($name), 'lock');
		if (touch($filename) === false || ($resource = fopen($filename, 'r')) === false) {
			throw new OpenFileFailedException($filename);
		}

		return new CriticalSection(new FlockMutex($resource));
	}
}
