<?php

namespace LimeSurvey\Api\Command\Response\Status;

abstract class StatusAbstract
{
    protected $code = null;

    public function getCode()
    {
        return $this->code;
    }
}
