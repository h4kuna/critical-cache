<?php declare(strict_types=1);


use h4kuna\CriticalCache\PSR16\NetteCache;

if (class_exists('Nette\Bridges\Psr\PsrCacheAdapter') === false) {
	class_alias(NetteCache::class, 'Nette\Bridges\Psr\PsrCacheAdapter');
}
