<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Services;

use h4kuna\CriticalCache\Interfaces\RandomGeneratorInterface;
use h4kuna\CriticalCache\Contracts\TokenServiceContract;
use h4kuna\CriticalCache\Contracts\UseOneTimeServiceContract;

final readonly class TokenService implements TokenServiceContract
{
	public function __construct(
		private UseOneTimeServiceContract $useOneTimeService,
		private RandomGeneratorInterface $tokenGenerator,
	) {
	}

	public function make(int $ttl = 900, string $value = self::CacheValue): string
	{
		$token = $this->tokenGenerator->execute();
		$this->useOneTimeService->save($token, $value, $ttl);

		return $token;
	}

	public function get(string $token): ?string
	{
		return $this->useOneTimeService->get($token);
	}

	public function compare(string $token, string $value = self::CacheValue): bool
	{
		return $this->get($token) === $value;
	}
}
