<?php

namespace LimeSurvey\Api\Command\Response\Status;

class StatusErrorForbidden extends StatusError
{
    public function __construct()
    {
        $this->code = 'error_forbidden';
    }
}
