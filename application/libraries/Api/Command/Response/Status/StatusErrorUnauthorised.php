<?php

namespace LimeSurvey\Api\Command\Response\Status;

class StatusErrorUnauthorised extends StatusAbstract
{
    public function __construct()
    {
        $this->code = 'error_unauthorised';
    }
}
