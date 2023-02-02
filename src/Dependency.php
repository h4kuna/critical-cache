<?php declare(strict_types=1);

namespace h4kuna\CriticalCache;

use h4kuna\CriticalCache\Exceptions\MissingDependencyException;
use Nette\Caching\Storages\FileStorage;
use Nette\Utils;
use League\Flysystem;

final class Dependency
{
	public static function checkNetteUtils(): void
	{
		if (class_exists(Utils\FileSystem::class) === false) {
			throw MissingDependencyException::create(Utils\FileSystem::class, 'nette/utils');
		}
	}


	public static function checkNetteCaching(): void
	{
		if (class_exists(FileStorage::class) === false) {
			throw MissingDependencyException::create(FileStorage::class, 'nette/caching');
		}
	}


	public static function checkLeagueFileSystem(): void
	{
		if (class_exists(Flysystem\Filesystem::class) === false) {
			throw MissingDependencyException::create(Flysystem\Filesystem::class, 'league/flysystem');
		}
	}

}
