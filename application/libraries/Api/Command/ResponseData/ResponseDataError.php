<?php

namespace LimeSurvey\Api\Command\ResponseData;

class ResponseDataError
{
    protected $code = null;
    protected $message = null;
    protected $data = [];

    public function __construct($code = 'unknown', $message = '', $data = [])
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    public function toArray()
    {
        return [
            'error' => [
                'code' => $this->code,
                'message' => $this->message,
                'data' => $this->data
            ]
        ];
    }
}
