<?php

namespace LimeSurvey\Api\Command\Response\Status;

class StatusErrorNotFound extends StatusAbstract
{
    public function __construct()
    {
        $this->code = 'error_not_found';
    }
}
