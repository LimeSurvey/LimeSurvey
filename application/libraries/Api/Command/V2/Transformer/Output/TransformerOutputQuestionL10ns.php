<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecordAbstract;

class TransformerOutputQuestionL10ns extends TransformerOutputActiveRecordAbstract
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
