<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\DataResponse\DataResponseFormatterInterface;
use Yiisoft\Http\Status;

final class NotFoundHandler implements RequestHandlerInterface
{
    private DataResponseFormatterInterface $formatter;
    private DataResponseFactoryInterface $dataResponseFactory;

    public function __construct(DataResponseFormatterInterface $formatter, DataResponseFactoryInterface $dataResponseFactory)
    {
        $this->formatter = $formatter;
        $this->dataResponseFactory = $dataResponseFactory;
    }

    // phpcs:ignore
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->formatter->format(
            $this->dataResponseFactory->createResponse('Page not found', Status::NOT_FOUND)
        );
    }
}
