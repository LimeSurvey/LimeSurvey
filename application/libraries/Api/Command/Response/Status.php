<?php

namespace LimeSurvey\Api\Command\Response;

abstract class Status
{
    /**
     * @var string
     */
    protected $code = 'unkown';

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
