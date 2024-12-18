<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Exceptions;

use Beste\Clock\SystemClock;
use h4kuna\Dir\Dir;
use Malkusch\Lock\Mutex\Mutex;
use Nette\Caching\Storages\FileStorage;
use RuntimeException;

final class MissingDependencyException extends RuntimeException
{

	public static function checkNetteCaching(): void
	{
		if (class_exists(FileStorage::class) === false) {
			throw self::create(FileStorage::class, 'nette/caching');
		}
	}

	private static function create(string $class, string $package): self
	{
		return new self("Missing class \"$class\", you can install by: composer require $package");
	}

	public static function checkH4kunaDir(): void
	{
		if (class_exists(Dir::class) === false) {
			throw self::create(Dir::class, 'h4kuna/dir');
		}
	}

	public static function checkBesteClock(): void
	{
		if (class_exists(SystemClock::class) === false) {
			throw self::create(SystemClock::class, 'beste/clock');
		}
	}

	public static function checkMalkuschLock(): void
	{
		if (class_exists(Mutex::class) === false && interface_exists(Mutex::class) === false) {
			throw self::create(Mutex::class, 'malkusch/lock');
		}
	}

}
