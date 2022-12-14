<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecordAbstract;

class TransformerOutputQuestionAttribute extends TransformerOutputActiveRecordAbstract
{
    public function transformAll($array)
    {
        return parent::transformAll(
            // Question->questionattributes relation is returned as an associative array keyed on 'attribute'
            // - so we need to call array_values to get the array of QuestionAttribute models
            array_values($array)
        );
    }

    protected function getDataMap()
    {
        return [
            'qaid' => true,
            'attribute' => true,
            'value' => true,
            'language' => true
        ];
    }
}
