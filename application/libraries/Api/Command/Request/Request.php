<?php

namespace LimeSurvey\Api\Command\Request;

class Request
{
    /**
     * @var array
     */
    private $data = array();

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->data['filesGlobal'] = $_FILES;
    }

    /**
     * @param ?string $key
     * @param ?mixed $defaultValue
     * @return ?mixed
     */
    public function getData($key = null, $defaultValue = null)
    {
        return $key && isset($this->data[$key])
            ? $this->data[$key]
            : $defaultValue;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setData($key, $value)
    {
        $this->data[$key] = $value;
    }
}
