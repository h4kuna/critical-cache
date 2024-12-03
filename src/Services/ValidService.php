<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Services;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use h4kuna\CriticalCache\Contracts\ValidServiceContract;
use h4kuna\CriticalCache\PSR16\Expire;
use h4kuna\CriticalCache\Utils\DateRangeStore;
use Psr\Clock\ClockInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * @phpstan-import-type TypeRange from DateRangeStore
 */
final class ValidService implements ValidServiceContract
{
	public function __construct(
		private readonly CacheInterface $cache,
		private readonly ClockInterface $clock,
	) {
	}

	public function isValid(string $key): bool
	{
		['from' => $from, 'to' => $to] = $this->decode($key);

		return $this->isValidCondition($from, $to);
	}

	public function value(string $key): ?string
	{
		['from' => $from, 'to' => $to, 'v' => $value] = $this->decode($key);

		return $this->isValidCondition($from, $to) ? $value : null;
	}

	/**
	 * @return TypeRange
	 */
	private function decode(string $key): array
	{
		$value = $this->cache->get($key);
		if (is_string($value) === false) {
			$value = '';
		}

		return DateRangeStore::decode($value);
	}

	public function from(string $key): ?DateTimeImmutable
	{
		return $this->decode($key)['from'];
	}

	public function remove(string $key): void
	{
		$this->cache->delete($key);
	}

	public function to(string $key): ?DateTimeImmutable
	{
		return $this->decode($key)['to'];
	}

	public function set(
		string $key,
		int|DateInterval|DateTimeInterface $validTo,
		int|DateInterval|DateTimeInterface|null $validFrom = null,
		string $value = '',
	): void {
		$dateTo = Expire::toDate($validTo, $this->clock);
		assert($dateTo instanceof DateTimeInterface);

		$this->cache->set($key, $this->encode($validFrom, $dateTo, $value), $this->clock->now()->diff($dateTo));
	}

	private function encode(int|DateInterval|DateTimeInterface|null $from, DateTimeInterface $to, string $value): string
	{
		return DateRangeStore::encode(Expire::toDate($from, $this->clock), $to, $value);
	}

	private function isValidCondition(?DateTimeImmutable $from, ?DateTimeImmutable $to): bool
	{
		return $to !== null && ($from === null || $from <= $this->clock->now());
	}
}
