<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;
use Throwable;
use Yiisoft\Http\Status;

final class ServerErrorException extends Exception implements ApplicationException
{
    public function __construct(string $message = 'Internal Server Error', int $code = Status::INTERNAL_SERVER_ERROR, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
