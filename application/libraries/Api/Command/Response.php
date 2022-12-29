<?php

namespace LimeSurvey\Api\Command;

use LimeSurvey\Api\Command\Response\Status;

class Response
{
    private $status = null;
    private $data = null;

    public function __construct($data, Status $status = null)
    {
        $this->status = $status;
        $this->data = $data;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getData()
    {
        return $this->data;
    }
}
