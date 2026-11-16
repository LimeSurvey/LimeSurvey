<?php

namespace LimeSurvey\Models\Services\Exception;

use LimeSurvey\Models\Services\Exception;
use Throwable;

class PersistErrorException extends Exception
{
    /**
     * @var int Code of a valid change the current state of the survey doesn't allow
     * (e.g. switching "Other" off while conditions use it): The editor drops the
     * change it already shows and shows the saved state again.
     */
    public const CODE_CHANGE_REFUSED = 409;

    public function __construct(
        $message = "",
        $code = 500,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
