<?php

namespace LimeSurvey\Models\Services\Exception;

use LimeSurvey\Models\Services\Exception;
use Throwable;

/**
 * The data was changed, or is being changed, by someone else since the client read it.
 */
class ConflictException extends Exception
{
    /**
     * Create the exception.
     *
     * @param string $message What conflicts
     * @param int $code HTTP status code that fits the error
     * @param Throwable|null $previous Exception that caused this one
     */
    public function __construct(
        $message = "",
        $code = 409,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
