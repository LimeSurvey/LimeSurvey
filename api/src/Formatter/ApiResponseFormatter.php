<?php

declare(strict_types=1);

namespace App\Formatter;

use App\Factory\ApiResponseDataFactory;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;

final class ApiResponseFormatter implements DataResponseFormatterInterface
{
    private ApiResponseDataFactory $apiResponseDataFactory;
    private JsonDataResponseFormatter $jsonDataResponseFormatter;

    public function __construct(
        ApiResponseDataFactory $apiResponseDataFactory,
        JsonDataResponseFormatter $jsonDataResponseFormatter
    ) {
        $this->apiResponseDataFactory = $apiResponseDataFactory;
        $this->jsonDataResponseFormatter = $jsonDataResponseFormatter;
    }

    public function format(DataResponse $dataResponse): ResponseInterface
    {
        $response = $dataResponse->withData(
            $this->apiResponseDataFactory->createFromResponse($dataResponse)->toArray(),
        );

        return $this->jsonDataResponseFormatter->format($response);
    }
}
