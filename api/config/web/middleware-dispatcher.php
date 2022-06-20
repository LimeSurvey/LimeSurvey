<?php

declare(strict_types=1);

use Yiisoft\Middleware\Dispatcher\MiddlewareFactoryInterface;
use Yiisoft\RequestModel\MiddlewareFactory;

/**
 * @var array $params
 */

return [
    MiddlewareFactoryInterface::class => MiddlewareFactory::class,
];
