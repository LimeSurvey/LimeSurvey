<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

class TransformerOutputQuestionAttribute extends TransformerOutputActiveRecordAbstract
{
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
