<?php

declare(strict_types=1);

use App\Formatter\ApiResponseFormatter;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\DataResponse\DataResponseFormatterInterface;
use Yiisoft\DataResponse\Formatter\HtmlDataResponseFormatter;
use Yiisoft\DataResponse\Formatter\XmlDataResponseFormatter;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;
use Yiisoft\DataResponse\Middleware\ContentNegotiator;

/* @var $params array */

return [
    DataResponseFormatterInterface::class => ApiResponseFormatter::class,
    DataResponseFactoryInterface::class => DataResponseFactory::class,
    ContentNegotiator::class => [
        '__construct()' => [
            'contentFormatters' => [
                'text/html' => new HtmlDataResponseFormatter(),
                'application/xml' => new XmlDataResponseFormatter(),
                'application/json' => new JsonDataResponseFormatter(),
            ],
        ],
    ],
];
