<?php

namespace LimeSurvey\Api\Transformer\Registry;

use LimeSurvey\Api\Transformer\Validators\Required;

class ValidationRegistry
{
    private array $data;

    public function __construct()
    {
        $this->data = [];
        $this->initDefault();
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        return null;
    }

    /**
     * @return array
     */
    public function initDefault()
    {
        $this->set('required', new Required());
    }
}
