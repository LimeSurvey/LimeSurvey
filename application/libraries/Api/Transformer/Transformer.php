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
     *
     * @param mixed $data
     * @return array
     */
    public function transform($data)
    {
        $dataMap = $this->getDataMap();

        $output = [];
        foreach ($dataMap as $dbField => $apiField) {
            if ($apiField === true) {
                $apiField = $dbField;
            }
            $output[$apiField] = $data[$dbField];
        }

        return $output;
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
