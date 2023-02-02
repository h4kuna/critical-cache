<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Exceptions;

final class MissingDependencyException extends \RuntimeException
{

	public static function create(string $class, string $package): self
	{
		return new self("Missing class \"$class\", you can install by: composer require $package");
	}

}
