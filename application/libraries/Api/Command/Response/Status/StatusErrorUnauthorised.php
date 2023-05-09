<?php

namespace LimeSurvey\Api\Command\Response\Status;

class StatusErrorUnauthorised extends StatusError
{
    public function __construct()
    {
        $this->code = 'error_unauthorised';
    }
}
