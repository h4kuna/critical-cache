<?php declare(strict_types=1);

namespace h4kuna\CriticalCache;

use h4kuna\CriticalCache\Exceptions\MissingDependencyException;
use h4kuna\Dir\Dir;
use malkusch\lock\mutex\LockMutex;
use Nette\Caching\Storages\FileStorage;

final class Dependency
{

	public static function checkNetteCaching(): void
	{
		if (class_exists(FileStorage::class) === false) {
			throw MissingDependencyException::create(FileStorage::class, 'nette/caching');
		}
	}


	public static function checkH4kunaDir(): void
	{
		if (class_exists(Dir::class) === false) {
			throw MissingDependencyException::create(Dir::class, 'h4kuna/dir');
		}
	}


	public static function checkMalkuschLock(): void
	{
		if (class_exists(LockMutex::class) === false) {
			throw MissingDependencyException::create(LockMutex::class, 'malkusch/lock');
		}
	}

}
