<?php

namespace LimeSurvey\Api\Command\Response\Status;

use LimeSurvey\Api\Command\Response\Status;

class StatusSuccessCreated extends Status
{
    public function __construct()
    {
        $this->code = 'success_created';
    }
}
