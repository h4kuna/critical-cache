<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Contracts;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;

interface ValidServiceContract
{
	public function isValid(string $key): bool;

	public function value(string $key): ?string;

	public function from(string $key): ?DateTimeImmutable;

	public function remove(string $key): void;

	public function to(string $key): ?DateTimeImmutable;

	public function set(
		string $key,
		int|DateInterval|DateTimeInterface $validTo,
		int|DateInterval|DateTimeInterface|null $validFrom = null,
		string $value = '',
	): void;
}
