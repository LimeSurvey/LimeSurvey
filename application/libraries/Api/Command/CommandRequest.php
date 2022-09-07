<?php

namespace LimeSurvey\Api\Command;

class CommandRequest
{
    private $data = array();

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getData($key = null, $defaultValue = null)
    {
        return ($key === null)
            ? $this->data
            : (isset($this->data[$key])
                ? $this->data[$key]
                : $defaultValue
            );
    }
}
