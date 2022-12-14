<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputQuestionAttribute extends TransformerOutputActiveRecord
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
