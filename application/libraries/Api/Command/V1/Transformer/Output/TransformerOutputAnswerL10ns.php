<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputAnswerL10ns extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $this->setDataMap([
            'id' => ['type' => 'int'],
            'aid' => ['type' => 'int'],
            'answer' => true,
            'language' => true
        ]);
    }
}
