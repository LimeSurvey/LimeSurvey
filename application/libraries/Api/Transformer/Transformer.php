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
            if (!isset($data[$key]) || $config === false) {
                continue;
            }

            $config = $this->normaliseConfig($config, $key);

            $output[$config['key']] = $this->cast(
                $data[$key],
                $config['type']
            );
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
        if ($config === true) {
            // map to same key name
            $key = $inputKey;
        } elseif (is_string($config) || is_int($config)) {
            // map to new key name
            $key = $config;
        } elseif (is_array($config)) {
            $key = isset($config['key']) ? $config['key'] : $inputKey;
            $type = isset($config['type']) ? $config['type'] : null;
        }

        return [
            'key' => $key,
            'type' => $type
        ];
    }

    /**
     *  Cast Value
     *
     * @param mixed $value
     * @param mixed $type
     * @return mixed
     */
    private function cast($value, $type)
    {
        if (!is_null($type)) {
            if (is_string($type)) {
                settype($value, $type);
            }
            if (is_callable($type)) {
                $value = $type($value);
            }
        }

        return $value;
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
