<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Services;

use h4kuna\CriticalCache\Contracts\PauseAfterUseContract;
use h4kuna\CriticalCache\Exceptions\NullIsNotAllowedException;
use h4kuna\CriticalCache\Interfaces\PauseServiceInterface;
use h4kuna\CriticalCache\PSR16\CacheLocking;
use Psr\SimpleCache\CacheInterface;

final readonly class PauseAfterUse implements PauseAfterUseContract
{
	public function __construct(private CacheLocking $cacheLocking) { }

	public function execute(PauseServiceInterface $pauseService): void
	{
		$key = $pauseService::class;
		do {
			$value = $this->cacheLocking->synchronized($key, function (CacheInterface $cache) use (
				$key,
				$pauseService,
			) {
				$value = $cache->get($key);

				if ($value !== null) {
					return $value;
				}

				$value = $pauseService->execute();
				if ($value === null) {
					throw new NullIsNotAllowedException();
				}
				$cache->set($key, $value, $pauseService->getCacheTtl());

				return null;
			});
		} while ($value !== null && $pauseService->wait($value));
	}
}
