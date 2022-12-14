<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputQuestionL10ns extends TransformerOutputActiveRecord
{
    protected function getDataMap()
    {
        return [
            'id' => true,
            'qid' => true,
            'question' => true,
            'script' => true,
            'language' => true
        ];
    }
}
