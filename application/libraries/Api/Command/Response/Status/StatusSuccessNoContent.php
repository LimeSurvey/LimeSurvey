<?php

namespace LimeSurvey\Api\Command\Response\Status;

use LimeSurvey\Api\Command\Response\Status;

class StatusSuccessNoContent extends Status
{
    public function __construct()
    {
        $this->code = 'success_no_content';
    }
}
