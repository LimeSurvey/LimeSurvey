<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputQuestionAttribute extends Transformer
{
    public function __construct()
    {
        $this->setDataMap([
            'qaid' => ['type' => 'int'],
            'qid' => ['type' => 'int'],
            'attribute' => true,
            'value' => true,
            'language' => true
        ]);
    }
}
