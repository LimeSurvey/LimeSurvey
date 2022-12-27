<?php

namespace LimeSurvey\Api\Transformer;

class Transformer implements TransformerInterface
{
    protected $dataMap = null;

    /**
     * Transform data
     *
     * Transforms data from one format to another.
     *
     * Default functionality is to map input data to output data using a data map.
     * Data map config also allows specification of type cast or callable formatter.
     *
     * @param mixed $data
     * @return array
     */
    public function transform($data)
    {
        $dataMap = $this->getDataMap();

        $output = [];
        foreach ($dataMap as $key => $config) {
            if (!array_key_exists($key, $data) || $config === false) {
                continue;
            }

            $config = $this->normaliseConfig($config, $key);

            $value = $this->cast(
                $data[$key],
                $config
            );

            // Null value reverts to default value
            // - the default value itself defaults to null
            if (is_null($value) && isset($config['default'])) {
                $value = $config['default'];
            }

            $this->validate($key, $value, $config);

            $output[$config['key']] = $value;
        }

        return $output;
    }

    /**
     * Normalise config
     *
     * @param bool|string|array $config
     * @param string|int $inputKey
     * @return array
     */
    private function normaliseConfig($config, $inputKey)
    {
        $key = null;
        $type = null;
        $default = null;
        $null = true;
        $empty = true;

        if ($config === true) {
            // map to same key name
            $key = $inputKey;
        } elseif (is_string($config) || is_int($config)) {
            // map to new key name
            $key = $config;
        } elseif (is_array($config)) {
            $key = isset($config['key']) ? $config['key'] : $inputKey;
            $type = isset($config['type']) ? $config['type'] : null;
            $default = isset($config['default']) ? (bool) $config['default'] : null;
            $null = isset($config['null']) ? (bool) $config['null'] : true;
            $empty = isset($config['empty']) ? (bool) $config['empty'] : true;
        }

        return [
            'key' => $key,
            'type' => $type,
            'default' => $default,
            'null' => $null,
            'empty' => $empty
        ];
    }

    /**
     *  Cast Value
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
     *  Validate Value
     *
     *  @param string $key
     * @param mixed $value
     * @param array $config
     * @throws \Exception
     * @return void
     */
    private function validate($key, $value, $config)
    {
        if ($value === null && $config['null'] === false) {
            throw new \Exception($key . ' cannot be null');
        }

        if (empty($value) && $config['empty'] === false) {
            throw new \Exception($key . ' cannot be empty');
        }
    }

    /**
     * Transform array of data
     *
     * @param array $data
     * @return array
     */
    public function transformAll(array $array)
    {
        return is_array($array) ? array_map( function ($data) {
            return $this->transform($data);
        }, $array) : null;
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
