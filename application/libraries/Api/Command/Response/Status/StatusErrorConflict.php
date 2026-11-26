<?php

namespace LimeSurvey\Api\Command\Response\Status;

/**
 * The request conflicts with the current state of the resource (HTTP 409).
 */
class StatusErrorConflict extends StatusError
{
    /**
     * Create the status.
     */
    public function __construct()
    {
        $this->code = 'error_conflict';
    }
}
