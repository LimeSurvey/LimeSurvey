<?php

declare(strict_types=1);

namespace App\Provider;

return [
    \Yiisoft\Cache\CacheInterface::class => \Yiisoft\Cache\Cache::class,
    \Psr\SimpleCache\CacheInterface::class => \Yiisoft\Cache\File\FileCache::class,
];
