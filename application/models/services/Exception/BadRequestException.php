<?php

namespace LimeSurvey\Models\Services\Exception;

use LimeSurvey\Models\Services\Exception;

class BadRequestException extends Exception
{
    public function __construct(
        $message = "",
        $code = 400,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
