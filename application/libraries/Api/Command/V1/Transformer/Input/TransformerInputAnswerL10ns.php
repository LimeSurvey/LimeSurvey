<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputAnswerL10ns extends Transformer
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
