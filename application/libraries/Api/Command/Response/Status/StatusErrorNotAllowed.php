<?php

namespace LimeSurvey\Api\Command\Response\Status;

class StatusErrorNotAllowed extends StatusError
{
    public function __construct()
    {
        $this->code = 'error_not_allowed';
    }
}
