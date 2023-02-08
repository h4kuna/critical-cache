<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Exceptions;

use h4kuna\Dir\Dir;
use malkusch\lock\mutex\LockMutex;
use Nette\Caching\Storages\FileStorage;

final class MissingDependencyException extends \RuntimeException
{

	public static function checkNetteCaching(): void
	{
		if (class_exists(FileStorage::class) === false) {
			throw self::create(FileStorage::class, 'nette/caching');
		}
	}


	public static function checkH4kunaDir(): void
	{
		if (class_exists(Dir::class) === false) {
			throw self::create(Dir::class, 'h4kuna/dir');
		}
	}


	public static function checkMalkuschLock(): void
	{
		if (class_exists(LockMutex::class) === false) {
			throw self::create(LockMutex::class, 'malkusch/lock');
		}
	}


	private static function create(string $class, string $package): self
	{
		return new self("Missing class \"$class\", you can install by: composer require $package");
	}

}
