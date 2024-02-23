<?php

namespace LimeSurvey\Api\Transformer\Registry;

use LimeSurvey\Api\Transformer\Filter\Filter;
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
use LimeSurvey\Api\Transformer\Validators\ValidatorInterface;

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
     * Loops through all available validators and applies the validation.
     * Returns array of errors.
     * @param string $key
     * @param mixed $value
     * @param array $config
     * @param array $data
     * @param array $options
     * @return array
     */
    public function validate($key, $value, $config, $data, $options = [])
    {
        $errors = [];
        foreach ($this->validators as $validator) {
            /** @var ValidatorInterface $validator */
            $result = $validator->validate($key, $value, $config, $data, $options);
            if (is_array($result)) {
                $errors[$key][] = $result;
            }
        }
        return $errors;
    }

    /**
     * If the config contains a formatter option,
     * it loops through all available formatters and attempts to format
     * the value. The loop will stop when a formatter actually formats the value,
     * as there can be only one formatter per key.
     * @param mixed $value
     * @param array $config
     * @return mixed
     */
    public function format($value, $config)
    {
        if (array_key_exists('formatter', $config)) {
            foreach ($this->formatters as $formatter) {
                /* @var FormatterInterface $formatter */
                $value = $formatter->format($value, $config);
                if ($formatter->isActive()) {
                    break;
                }
            }
        }

        return $value;
    }

    /**
     * If the config contains a filter option,
     * it will apply the filter to the value.
     * @param mixed $value
     * @param array $config
     * @return mixed
     */
    public function filter($value, $config)
    {
        if (isset($config['filter']) && !is_null($value)) {
            $filter = new Filter($config['filter']);
            return $filter->filter($value);
        }
        return $value;
    }
}
