<?php

namespace LimeSurvey\Api\Command;

class CommandResponse
{
    private $data = array();

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
