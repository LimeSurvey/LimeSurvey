<?php

namespace LimeSurvey\Api\Transformer\Registry;

use LimeSurvey\Api\Transformer\Formatter\FormatterDateTimeToJson;
use LimeSurvey\Api\Transformer\Formatter\FormatterInterface;
use LimeSurvey\Api\Transformer\Formatter\FormatterIntToBool;
use LimeSurvey\Api\Transformer\Formatter\FormatterMandatory;
use LimeSurvey\Api\Transformer\Formatter\FormatterYnToBool;
use LimeSurvey\Api\Transformer\Validators\DateValidator;
use LimeSurvey\Api\Transformer\Validators\EmptyValidator;
use LimeSurvey\Api\Transformer\Validators\LengthValidator;
use LimeSurvey\Api\Transformer\Validators\NullValidator;
use LimeSurvey\Api\Transformer\Validators\NumericalValidator;
use LimeSurvey\Api\Transformer\Validators\RangeValidator;
use LimeSurvey\Api\Transformer\Validators\RegexValidator;
use LimeSurvey\Api\Transformer\Validators\RequiredValidator;

/**
 * Handles the registering of validators and formatters
 */
class Registry
{
    private array $validators;
    private array $formatters;

    public function __construct()
    {
        $this->validators = [];
        $this->formatters = [];
        $this->initDefault();
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setValidator($key, $value): void
    {
        $this->validators[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getValidator($key)
    {
        if (isset($this->validators[$key])) {
            return $this->validators[$key];
        }
        return null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setFormatter($key, $value): void
    {
        $this->formatters[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getFormatter($key)
    {
        if (isset($this->formatters[$key])) {
            return $this->formatters[$key];
        }
        return null;
    }

    /**
     * @return void
     */
    private function initDefault()
    {
        // Validators
        $this->setValidator('required', new RequiredValidator());
        $this->setValidator('null', new NullValidator());
        $this->setValidator('empty', new EmptyValidator());
        $this->setValidator('length', new LengthValidator());
        $this->setValidator('range', new RangeValidator());
        $this->setValidator('numerical', new NumericalValidator());
        $this->setValidator('pattern', new RegexValidator());
        $this->setValidator('date', new DateValidator());
        // Formatters
        $this->setFormatter('dateTimeToJson', new FormatterDateTimeToJson());
        $this->setFormatter('ynToBool', new FormatterYnToBool());
        $this->setFormatter('intToBool', new FormatterIntToBool());
        $this->setFormatter('mandatory', new FormatterMandatory());
    }

    /**
     * @param array $config
     * @param ?array $options
     * @return array
     */
    public function normaliseConfig(array $config, $options = []): array
    {
        foreach ($this->validators as $name => $validator) {
            $config[$name] = $validator->normaliseConfigValue(
                $config,
                $options
            );
        }
        // There can be only one formatter
        foreach ($this->formatters as $formatter) {
            if (!isset($config['formatter']) ||  !($config['formatter'] instanceof FormatterInterface)) {
                $config['formatter'] = $formatter->normaliseConfigValue(
                    $config,
                    $options
                );
            }
        }

        return $config;
    }

    /**
     * @param mixed $value
     * @param array $config
     * @return mixed
     */
    public function format($value, $config)
    {
        if (
            isset($config['formatter'])
            && $config['formatter'] instanceof FormatterInterface
        ) {
            $value = $config['formatter']->format($value);
        }

        return $value;
    }
}
