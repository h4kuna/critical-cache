<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Caching;

use DateInterval;
use DateTimeInterface;
use h4kuna\CriticalCache\Caching\ValidityAware\TimeRangeEncoder;
use h4kuna\CriticalCache\Caching\ValidityAware\TimeRangeItem;
use h4kuna\CriticalCache\Contracts\ValidityAwareCacheContract;
use h4kuna\CriticalCache\Exceptions\LogicException;
use h4kuna\CriticalCache\PSR16\Expire;
use Psr\Clock\ClockInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * @phpstan-import-type TypeSave from TimeRangeEncoder
 */
final readonly class ValidityAwareCache implements ValidityAwareCacheContract
{
	public function __construct(
		private CacheInterface $cache,
		private ClockInterface $clock,
		private TimeRangeEncoder $timeRangeEncoder,
	) {
	}

	public function get(string $key): TimeRangeItem
	{
		/** @var TypeSave|null $value */
		$value = $this->cache->get($key);

		return $this->timeRangeEncoder->decode($value);
	}

	public function delete(string $key): void
	{
		$this->cache->delete($key);
	}

	public function set(
		string $key,
		int|DateInterval|DateTimeInterface $validTo,
		int|DateInterval|DateTimeInterface|null $validFrom = null,
		string $value = '',
	): void {

		$validFromDate = Expire::toDate($validFrom, $this->clock);
		$validToDate = Expire::toDate($validTo, $this->clock);

		if ($validFromDate !== null && $validFromDate >= $validToDate) {
			throw new LogicException('Parameters $from and $to are invalid, because $from is higher or equal to $to.');
		}

		$now = $this->clock->now();
		if ($validFromDate !== null) {
			$diffFrom = $validFromDate->getTimestamp() - $now->getTimestamp();
			if ($diffFrom > 0) {
				$validToDate = $now->setTimestamp($validToDate->getTimestamp() + $diffFrom);
			}
		}

		$this->cache->set($key, $this->timeRangeEncoder->encode($validFromDate, $validToDate, $value), $now->diff($validToDate));
	}

}
