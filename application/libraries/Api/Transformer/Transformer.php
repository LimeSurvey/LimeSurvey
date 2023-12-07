<?php

namespace LimeSurvey\Api\Transformer;

use LimeSurvey\Api\Transformer\Formatter\FormatterInterface;

class Transformer implements TransformerInterface
{
    /** @var array */
    protected $dataMap = [];

    /**
     * Transform data
     *
     * Transforms data from one format to another.
     *
     * Default functionality is to map input data to output
     * data (or vice versa) using a data map.
     * Data map config also allows specification of type cast
     * and callable formatter.
     *
     * @param ?mixed $data
     * @return ?mixed
     * @throws TransformerException
     */
    public function transform($data)
    {
        $dataMap = $this->getDataMap();
        $output = null;
        foreach ($dataMap as $key => $config) {
            if (!$config) {
                continue;
            }
            $config = $this->normaliseConfig($config, $key);
            $value = isset($data[$key])
                ? $this->cast($data[$key], $config)
                : null;
            // Null value reverts to default value
            // - the default value itself defaults to null
            if (is_null($value) && isset($config['default'])) {
                $value = $config['default'];
            }
            // Null value and null default
            // - skip if not required
            if (is_null($value) && !$config['required']) {
                continue;
            }

            $value = $this->format($value, $config);
            $errors = $this->validateKey(
                $key,
                $value,
                $config,
                $data
            );
            if (is_array($errors)) {
                throw new TransformerException($errors[0]);
            }

            if (
                $config['transformer'] instanceof TransformerInterface
                && isset($value)
            ) {
                $transformMethod = $config['collection'] ? 'transformAll' : 'transform';
                $value = $config['transformer']->{$transformMethod}($value);
            }

            if (!isset($output)) {
                $output = [];
            }
            $output[$config['key']] = $value;
        }
        return $output;
    }

    /**
     * Normalise config
     *
     * @param bool|int|string|array $config
     * @param string|int $inputKey
     * @return array
     */
    private function normaliseConfig($config, $inputKey)
    {
        if ($config === true) {
            // map to same key name
            $key = $inputKey;
        } elseif (is_string($config) || is_int($config)) {
            // map to new key name
            $key = $config;
        } elseif (is_array($config)) {
            $key = isset($config['key']) ? $config['key'] : $inputKey;
            $type = isset($config['type']) ? $config['type'] : null;
            $collection = isset($config['collection']) ? $config['collection'] : false;
            $transformer = isset($config['transformer']) ? $config['transformer'] : null;
            $formatter = isset($config['formatter']) ? $config['formatter'] : null;
            $default = isset($config['default']) ? (bool) $config['default'] : null;
            $required = isset($config['required']) ? (bool) $config['required'] : false;
            $null = isset($config['null']) ? (bool) $config['null'] : true;
            $empty = isset($config['empty']) ? (bool) $config['empty'] : true;
        }

        return [
            'key' => $key ?? null,
            'type' => $type ?? null,
            'collection' => $collection ?? false,
            'transformer' => $transformer ?? null,
            'formatter' => $formatter ?? null,
            'default' => $default ?? null,
            'required' => $required ?? false,
            'null' => $null ?? true,
            'empty' => $empty ?? true
        ];
    }

    /**
     * Cast Value
     *
     * @param mixed $value
     * @param array $config
     * @return mixed
     */
    private function cast($value, $config)
    {
        $type = $config['type'];
        if (!empty($type)) {
            if (is_callable($type)) {
                $value = $type($value);
            } elseif (is_string($type)) {
                settype($value, $type);
            }
        }

        return $value;
    }

    /**
     * Format Value
     *
     * @param mixed $value
     * @param array $config
     * @return mixed
     */
    private function format($value, $config)
    {
        if (
            isset($config['formatter'])
            && $config['formatter'] instanceof FormatterInterface
        ) {
            $value = $config['formatter']->format($value);
        }
        return $value;
    }

    /**
     * @param ?mixed $data
     * @return boolean|array Returns true on success or array of errors.
     */
    public function validate($data)
    {
        $dataMap = $this->getDataMap();

        $errors = [];

        foreach ($dataMap as $key => $config) {
            if (!$config) {
                continue;
            }
            $config = $this->normaliseConfig($config, $key);
            $value = isset($data[$key])
                ? $this->cast($data[$key], $config)
                : null;
            $value = $this->format($value, $config);

            // Null value reverts to default value
            // - the default value itself defaults to null
            if (is_null($value) && isset($config['default'])) {
                $value = $config['default'];
            }

            $fieldErrors = $this->validateKey(
                $key,
                $value,
                $config,
                $data
            );
            if (is_array($fieldErrors)) {
                $errors = array_merge($errors, $fieldErrors);
            }
            if (
                $config['transformer'] instanceof TransformerInterface
                && isset($value)
            ) {
                $validateMethod = $config['collection'] ? 'validateAll' : 'validate';
                $subFieldErrors = $config['transformer']->{$validateMethod}($value);
                if (is_array($subFieldErrors)) {
                    $errors = array_merge($errors, $subFieldErrors);
                }
            }
        }

        return empty($errors) ?: $errors;
    }

    /**
     * Validate Value
     *
     * Returns boolean true or array of errors.
     *
     * @param string $key
     * @param mixed $value
     * @param array $config
     * @param array $data
     * @return boolean|array
     */
    private function validateKey($key, $value, $config, $data)
    {
        $errors = [];
        if (
            $config['required']
            && (
                !is_array($data)
                || !array_key_exists($key, $data)
            )
        ) {
            $errors[] = $key . ' is required';
        }

        if ($value === null && $config['null'] === false) {
            $errors[] = $key . ' cannot be null';
        }

        if (empty($value) && $config['empty'] === false) {
            $errors[] = $key . ' cannot be empty';
        }

        return empty($errors) ?: $errors;
    }

    /**
     * Transform collection
     *
     * @param array $collection
     * @return array
     */
    public function transformAll($collection)
    {
        return is_array($collection) ? array_map(function ($allData) {
            return $this->transform($allData);
        }, $collection) : [];
    }

    /**
     * Validate collection
     *
     * @param array $collection
     * @return bool|array Returns true on success or array of errors.
     */
    public function validateAll($collection)
    {
        $result = array_reduce($collection, function ($carry, $data) {
            $oneResult = $this->validate($data);
            return is_array($oneResult)
                ? array_merge($carry, $oneResult)
                : $carry;
        }, []);
        return empty($result) ?: $result;
    }

    /**
     * Get data map
     *
     * @param array $dataMap
     * @return array
     */
    public function getDataMap()
    {
        return $this->dataMap ? $this->dataMap : [];
    }

    /**
     * Set data map
     *
     * Data map is an associative array.
     * Key is the key in the source data and value is either boolean true
     * - to include the data as is or a a string field name to map the value
     * - to in the output.
     *
     * @param array $dataMap
     * @return void
     */
    public function setDataMap(array $dataMap)
    {
        $this->dataMap = $dataMap;
    }
}
