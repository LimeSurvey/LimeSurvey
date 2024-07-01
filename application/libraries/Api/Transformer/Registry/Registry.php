<?php

namespace LimeSurvey\Api\Transformer\Registry;

use LimeSurvey\Api\Transformer\{
    Filter\Filter,
    Formatter\FormatterDateTimeToJson,
    Formatter\FormatterIntToBool,
    Formatter\FormatterMandatory,
    Formatter\FormatterYnToBool,
    Validator\ValidatorDate,
    Validator\ValidatorEmpty,
    Validator\ValidatorLength,
    Validator\ValidatorNull,
    Validator\ValidatorNumerical,
    Validator\ValidatorRange,
    Validator\ValidatorRegex,
    Validator\ValidatorRequired,
};

/**
 * Handles the registering of validators and formatters
 */
class Registry
{
    private array $validators;
    private array $formatters;
    private array $filters;

    public function __construct()
    {
        $this->validators = [];
        $this->formatters = [];
        $this->filters = [];
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

    public function getAllValidators(): array
    {
        return $this->validators;
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
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setFilter($key, $value): void
    {
        $this->filters[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getFilter($key = 'filter')
    {
        if (isset($this->filters[$key])) {
            return $this->filters[$key];
        }
        return null;
    }

    /**
     * @return void
     */
    private function initDefault()
    {
        // Validators
        $this->setValidator('required', new ValidatorRequired());
        $this->setValidator('null', new ValidatorNull());
        $this->setValidator('empty', new ValidatorEmpty());
        $this->setValidator('length', new ValidatorLength());
        $this->setValidator('range', new ValidatorRange());
        $this->setValidator('numerical', new ValidatorNumerical());
        $this->setValidator('pattern', new ValidatorRegex());
        $this->setValidator('date', new ValidatorDate());
        // Formatters
        $this->setFormatter('dateTimeToJson', new FormatterDateTimeToJson());
        $this->setFormatter('ynToBool', new FormatterYnToBool());
        $this->setFormatter('intToBool', new FormatterIntToBool());
        $this->setFormatter('mandatory', new FormatterMandatory());
        // Filters
        $this->setFilter('filter', new Filter());
    }
}
