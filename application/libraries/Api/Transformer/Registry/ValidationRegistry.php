<?php

namespace LimeSurvey\Api\Transformer\Registry;

use LimeSurvey\Api\Transformer\Validators\DateValidator;
use LimeSurvey\Api\Transformer\Validators\EmptyValidator;
use LimeSurvey\Api\Transformer\Validators\LengthValidator;
use LimeSurvey\Api\Transformer\Validators\NullValidator;
use LimeSurvey\Api\Transformer\Validators\NumericalValidator;
use LimeSurvey\Api\Transformer\Validators\RangeValidator;
use LimeSurvey\Api\Transformer\Validators\RegexValidator;
use LimeSurvey\Api\Transformer\Validators\RequiredValidator;

class ValidationRegistry
{
    private array $data;

    public function __construct()
    {
        $this->data = [];
        $this->initDefault();
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        return null;
    }

    /**
     * @return void
     */
    private function initDefault()
    {
        $this->set('required', new RequiredValidator());
        $this->set('null', new NullValidator());
        $this->set('empty', new EmptyValidator());
        $this->set('length', new LengthValidator());
        $this->set('range', new RangeValidator());
        $this->set('numerical', new NumericalValidator());
        $this->set('pattern', new RegexValidator());
        $this->set('date', new DateValidator());
    }

    /**
     * @param array $config
     * @param ?array $options
     * @return array
     */
    public function normaliseConfig(array $config, $options = []): array
    {
        foreach ($this->data as $name => $validator) {
            $config[$name] = $validator->normaliseConfigValue(
                $config,
                $options
            );
        }

        return $config;
    }
}
