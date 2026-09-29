<?php

namespace LimeSurvey\Api\Command\Response\Status;

use LimeSurvey\Api\Command\Response\Status;

class StatusSuccess extends Status
{
    public function __construct()
    {
        $this->code = 'success';
    }
}
