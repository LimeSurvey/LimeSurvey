<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputQuestionL10ns extends Transformer
{
    use TransformerInputLanguageTrait;

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
