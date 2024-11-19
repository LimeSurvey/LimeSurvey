<?php

namespace LimeSurvey\Models\Services\Exception;

use LimeSurvey\Models\Services\Exception;

class NotFoundException extends Exception
{
    public function __construct(
        $message = "",
        $code = 404,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
