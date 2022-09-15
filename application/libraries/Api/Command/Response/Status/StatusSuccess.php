<?php

namespace LimeSurvey\Api\Command\Response\Status;

class StatusSuccess extends StatusAbstract
{
    public function __construct()
    {
        $this->code = 'success';
    }
}
