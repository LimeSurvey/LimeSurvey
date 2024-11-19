<?php

namespace LimeSurvey\Api\Command\Response;

use LimeSurvey\Api\Command\Response\Status;

class Response
{
    private Status $status;

    /**
     * @var mixed
     */
    private $data = null;

     /**
     * @param mixed $data
     * @param Status $status
     */
    public function __construct($data, Status $status)
    {
        $this->status = $status;
        $this->data = $data;
    }

    /**
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
