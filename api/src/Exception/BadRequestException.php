<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;
use Throwable;
use Yiisoft\Http\Status;

final class BadRequestException extends Exception implements ApplicationException
{
    public function __construct(string $message = 'Bad request', int $code = Status::BAD_REQUEST, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
