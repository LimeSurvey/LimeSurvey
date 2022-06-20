<?php

declare(strict_types=1);

use App\Handler\NotFoundHandler;
use Yiisoft\Definitions\DynamicReference;
use Yiisoft\Definitions\Reference;
use Yiisoft\Injector\Injector;
use Yiisoft\Middleware\Dispatcher\MiddlewareDispatcher;

/** @var array $params */

return [
    Yiisoft\Yii\Http\Application::class => [
        '__construct()' => [
            'dispatcher' => DynamicReference::to(static function (Injector $injector) use ($params) {
                return ($injector->make(MiddlewareDispatcher::class))
                    ->withMiddlewares($params['middlewares']);
            }),
            'fallbackHandler' => Reference::to(NotFoundHandler::class),
        ],
    ],
];
