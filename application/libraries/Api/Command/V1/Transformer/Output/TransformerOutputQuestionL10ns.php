<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputQuestionL10ns extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $this->setDataMap([
            'id' => ['type' => 'int'],
            'qid' => ['type' => 'int'],
            'question' => true,
            'help' => true,
            'script' => true,
            'language' => true
        ]);
    }
}
