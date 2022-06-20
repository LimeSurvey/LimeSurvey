<?php

declare(strict_types=1);

use App\Middleware\ExceptionMiddleware;
use Yiisoft\Config\Config;
use Yiisoft\DataResponse\Middleware\FormatDataResponse;
use Yiisoft\Request\Body\RequestBodyParser;
use Yiisoft\Router\Group;
use Yiisoft\Router\RouteCollection;
use Yiisoft\Router\RouteCollectionInterface;
use Yiisoft\Router\RouteCollectorInterface;

/** @var Config $config */

return [
    RouteCollectionInterface::class => static function (RouteCollectorInterface $collector) use ($config) {
        $collector
            ->middleware(RequestBodyParser::class)
            ->middleware(FormatDataResponse::class)
            ->middleware(ExceptionMiddleware::class)
            ->addGroup(Group::create()->routes(...$config->get('routes')));

        return new RouteCollection($collector);
    },
];
