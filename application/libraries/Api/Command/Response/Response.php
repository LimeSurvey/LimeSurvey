<?php

namespace LimeSurvey\Api\Command\Response;

use LimeSurvey\Api\Command\Response\Status\StatusAbstract;

class Response
{
    private $status = null;
    private $data = null;

    public function __construct($data, StatusAbstract $status = null)
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
