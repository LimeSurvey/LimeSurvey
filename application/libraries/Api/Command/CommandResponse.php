<?php

namespace LimeSurvey\Api\Command;

class CommandResponse
{
    private $data = null;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
