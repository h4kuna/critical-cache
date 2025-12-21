<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Services;

use DateInterval;
use DateTimeImmutable;
use h4kuna\CriticalCache\Contracts\UseOneTimeServiceContract;
use h4kuna\CriticalCache\Contracts\ValidServiceContract;
use h4kuna\CriticalCache\PSR16\Expire;

final readonly class UseOneTimeService implements UseOneTimeServiceContract
{
	public function __construct(
		private ValidServiceContract $validService,
	) {
	}

	public function save(
		string $key,
		string $value,
		int|DateInterval $ttl = 900,
		?DateTimeImmutable $validFrom = null,
	): string {
		if ($validFrom !== null && is_int($ttl) === true) {
			$ttl += Expire::after($validFrom);
		}

		$this->validService->set($key, $ttl, $validFrom, $value);

		return $value;
	}

	public function get(string $key): ?string
	{
		$stored = $this->validService->value($key);
		if ($stored === null) {
			return null;
		}

		$this->validService->remove($key);

		return $stored;
	}
}
