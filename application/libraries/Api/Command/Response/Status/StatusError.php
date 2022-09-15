<?php

namespace LimeSurvey\Api\Command\Response\Status;

class StatusError extends StatusAbstract
{
    public function __construct()
    {
        $this->code = 'error';
    }
}
