<?php

declare(strict_types=1);

use App\Auth\AuthRequestErrorHandler;
use App\Auth\TokenRepository;
use Yiisoft\Auth\AuthenticationMethodInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\Auth\IdentityWithTokenRepositoryInterface;
use Yiisoft\Auth\Method\HttpHeader;
use Yiisoft\Auth\Middleware\Authentication;
use Yiisoft\Definitions\Reference;
use Yiisoft\Auth\Method\HttpBearer;

/** @var array $params */

return [
    IdentityRepositoryInterface::class => TokenRepository::class,
    IdentityWithTokenRepositoryInterface::class => TokenRepository::class,
    AuthenticationMethodInterface::class => HttpBearer::class,
    Authentication::class => [
        'class' => Authentication::class,
        '__construct()' => [
            'authenticationFailureHandler' => Reference::to(AuthRequestErrorHandler::class),
        ],
    ],
];
