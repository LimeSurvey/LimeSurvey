<?php

namespace LimeSurvey\Api\Command\Response\Status;

class StatusSuccessCreated extends StatusAbstract
{
    public function __construct()
    {
        $this->code = 'success_created';
    }
}
