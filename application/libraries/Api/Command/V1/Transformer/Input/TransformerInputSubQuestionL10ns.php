<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputSubQuestionL10ns extends Transformer
{
    public function __construct()
    {
        $this->setDataMap([
            'question' => ['required' => true],
        ]);
    }
}
