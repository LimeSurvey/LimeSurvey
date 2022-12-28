<?php

namespace LimeSurvey\Api\Command\Response;

abstract class Status
{
    protected $code = 'unkown';

    public function getCode()
    {
        return $this->code;
    }
}
