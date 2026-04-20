<?php

namespace LimeSurvey\Models\Services\Exception;

use LimeSurvey\Models\Services\Exception;
use Throwable;

class PermissionDeniedException extends Exception
{
    public function __construct(
        $message = "",
        $code = 403,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
