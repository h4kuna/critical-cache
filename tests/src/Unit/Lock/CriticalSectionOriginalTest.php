<?php

declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Unit\Lock;

use h4kuna\CriticalCache\Lock\Malkusch\CriticalSectionOriginal;
use h4kuna\Dir\TempDir;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class CriticalSectionOriginalTest extends TestCase
{
	public function testBasic(): void
	{
		$original = new CriticalSectionOriginal(new TempDir());

		$lock1 = $original->get('foo');
		$lock2 = $original->get('foo');
		$lock3 = $original->get('bar');

		Assert::same($lock1, $lock2);
		Assert::notSame($lock2, $lock3);
	}
}

(new CriticalSectionOriginalTest)->run();
