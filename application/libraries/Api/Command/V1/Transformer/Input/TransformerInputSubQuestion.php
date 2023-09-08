<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputSubQuestion extends Transformer
{
    public function __construct()
    {
        $this->setDataMap([
            'oldCode' => 'oldcode',
            'code' => true,
            'relevance' => true,
            'subQuestionL10n' => 'subquestionl10n'
        ]);
    }
}
