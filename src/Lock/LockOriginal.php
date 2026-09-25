<?php declare(strict_types = 1);

namespace h4kuna\CriticalCache\Lock;

interface LockOriginal
{

	public function get(string $name): Lock;

}
