<?php

namespace LimeSurvey\Api\Command\ResponseData;

class ResponseDataError
{
    /**
     * @var string|int|null
     */
    protected $code = null;

    /**
     * @var ?string
     */
    protected $message = null;

    /**
     * @var mixed
     */
    protected $data = [];

    /**
     * @param string $code
     * @param string $message
     * @param mixed $data
     */
    public function __construct($code = 'unknown', $message = '', $data = [])
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * @return array
     */
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
