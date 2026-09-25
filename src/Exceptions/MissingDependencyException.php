<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Exceptions;

use Beste\Clock\SystemClock;
use h4kuna\Dir\Dir;
use Nette\Caching\Storages\FileStorage;
use RuntimeException;
use Symfony\Component\Lock\LockFactory;
use function class_exists;

final class MissingDependencyException extends RuntimeException
{

	public static function checkNetteCaching(): void
	{
		if (class_exists(FileStorage::class) === false) {
			throw self::create(FileStorage::class, 'nette/caching');
		}
	}

	private static function create(
		string $class,
		string $package,
	): self
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

	public static function checkSymfonyLock(): void
	{
		if (class_exists(LockFactory::class) === false) {
			throw self::create(LockFactory::class, 'symfony/lock');
		}
	}

}
