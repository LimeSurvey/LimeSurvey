<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

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
        return array_map( function($data){
            return $this->transform($data);
        }, $array);
    }

    protected function getDataMap()
    {
        return [];
    }
}
