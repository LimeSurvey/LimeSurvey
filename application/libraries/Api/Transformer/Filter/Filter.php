<?php

namespace LimeSurvey\Api\Transformer\Filter;

/**
 * Filters are applied before validation.
 * example config:
 * 'adminEmail' => ['filter' => 'trim']
 * which would result in trim($value)
 * or
 * 'adminEmail' => ['filter' => ['trim' => [' e!%§"']]]
 * which would result in trim($value, ' e!%§')
 */
class Filter
{
    /**
     * the filter method
     */
    private string $filter = '';

    /**
     * @var array optional parameters to be passed to the filter method
     */
    private $filterParams = [];

    /**
     * Tries to execute a data transforming filter.
     * This can deal with basic php functions, e.g. trim, strtolower, etc
     * where the value is always passed as the first argument.
     *
     * @param ?mixed $value
     * @param array|string $config
     * @return ?mixed
     */
    public function filter($value, $config)
    {
        $this->filterParams = [];
        if (is_array($config) && !empty($config)) {
            $this->filter = strval(array_key_first($config));
            $this->filterParams = $config[$this->filter];
        } elseif (is_string($config) && !empty($config)) {
            $this->filter = $config;
        }
        $filteredValue = false;
        if (is_callable($this->filter)) {
            array_unshift($this->filterParams, $value);
            $filteredValue = call_user_func_array($this->filter, $this->filterParams);
        }
        return $filteredValue !== false ? $filteredValue : $value;
    }
}
