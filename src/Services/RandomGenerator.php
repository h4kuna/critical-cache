<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Services;

use h4kuna\CriticalCache\Contracts\RandomGeneratorContract;

/**
 * uuid v4 implementation
 * @see https://www.uuidgenerator.net/dev-corner/php
 *
 * implement own generator
 * @see https://github.com/ramsey/uuid
 */
final class RandomGenerator implements RandomGeneratorContract
{
	public function execute(): string
	{
		$data = random_bytes(16);

		$data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

		$uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
		assert($uuid !== '');

		return $uuid;
	}
}
