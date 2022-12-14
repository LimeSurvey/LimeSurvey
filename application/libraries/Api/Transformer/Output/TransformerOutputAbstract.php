<?php

namespace LimeSurvey\Api\Transformer\Output;

abstract class TransformerOutputAbstract implements TransformerOutputInterface
{
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

    public function transformAll($array)
    {
        return is_array($array) ? array_map( function ($data) {
            return $this->transform($data);
        }, $array) : null;
    }

    protected function getDataMap()
    {
        return [];
    }
}
