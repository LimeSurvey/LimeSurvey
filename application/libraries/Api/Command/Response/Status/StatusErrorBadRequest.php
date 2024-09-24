<?php

namespace LimeSurvey\Api\Command\Response\Status;

class StatusErrorBadRequest extends StatusError
{
    public function __construct()
    {
        $this->code = 'error_bad_request';
    }
}
