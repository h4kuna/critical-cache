<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Services;

use h4kuna\CriticalCache\Interfaces\RandomGeneratorInterface;
use h4kuna\CriticalCache\Contracts\TokenServiceContract;
use h4kuna\CriticalCache\Contracts\UseOneTimeServiceContract;
use DateTimeImmutable;

final readonly class TokenService implements TokenServiceContract
{
	public function __construct(
		private UseOneTimeServiceContract $useOneTimeService,
		private RandomGeneratorInterface $tokenGenerator,
	) {
	}

	public function make(int $ttl = 900, string $value = self::CacheValue, ?DateTimeImmutable $validFrom = null): string
	{
		$token = $this->tokenGenerator->execute();
		$this->useOneTimeService->set($token, $value, $ttl, $validFrom);

		return $token;
	}

	public function get(string $token): ?string
	{
		return $this->useOneTimeService->get($token);
	}

	public function isEqual(string $token, string $value = self::CacheValue): bool
	{
		return $this->useOneTimeService->isEqual($token, $value);
	}
}
