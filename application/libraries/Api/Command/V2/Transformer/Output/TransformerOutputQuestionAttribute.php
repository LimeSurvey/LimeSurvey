<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputQuestionAttribute extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $this->setDataMap([
            'qaid' => ['type' => 'int'],
            'attribute' => ['type' => 'int'],
            'value' => true,
            'language' => true
        ]);
    }
}
