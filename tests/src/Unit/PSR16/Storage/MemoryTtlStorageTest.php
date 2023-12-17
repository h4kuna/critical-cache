<?php

declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit\PSR16\Storage;

use h4kuna\CriticalCache\PSR16\Storage\MemoryTtlStorage;
use Nette\Caching\Cache;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

final class MemoryTtlStorageTest extends TestCase
{

	public function testBasic(): void
	{
		$storage = new MemoryTtlStorage();
		Assert::null($storage->read('a'));
		$storage->write('a', 'foo', [Cache::Expire => 1]);
		Assert::same('foo', $storage->read('a'));
		sleep(1);
		Assert::null($storage->read('a'));

		$storage->write('a', 'foo', []);
		$storage->write('b', 'foo', []);
		$storage->write('c', 'foo', []);
		$storage->remove('a');
		Assert::null($storage->read('a'));
		Assert::same('foo', $storage->read('b'));
		Assert::same('foo', $storage->read('c'));

		$storage->clean([Cache::All => true]);
		Assert::null($storage->read('a'));
		Assert::null($storage->read('b'));
		Assert::null($storage->read('c'));
	}

}

(new MemoryTtlStorageTest())->run();
