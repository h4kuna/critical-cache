<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Services;

use h4kuna\CriticalCache\Contracts\TokenGeneratorContract;
use h4kuna\CriticalCache\Contracts\TokenServiceContract;
use h4kuna\CriticalCache\Contracts\UseOneTimeServiceContract;

final readonly class TokenService implements TokenServiceContract
{
	public function __construct(
		private UseOneTimeServiceContract $useOneTimeService,
		private TokenGeneratorContract $tokenGenerator,
	) {
	}

	public function make(int $ttl = 900, string $value = self::CacheValue): string
	{
		$token = $this->tokenGenerator->generate();
		$this->useOneTimeService->save($token, $value, $ttl);

		return $token;
	}

	public function get(string $token): ?string
	{
		return $this->useOneTimeService->get($token);
	}

	public function compare(string $token, string $value = self::CacheValue): bool
	{
		return $this->useOneTimeService->get($token) === $value;
	}
}
