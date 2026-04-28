<?php

namespace LimeSurvey\Models\Services\Exception;

use LimeSurvey\Models\Services\Exception;

class PersistErrorException extends Exception
{
    public function __construct(
        $message = "",
        $code = 500,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
