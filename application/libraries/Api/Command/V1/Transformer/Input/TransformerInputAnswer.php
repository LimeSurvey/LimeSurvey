<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputAnswer extends Transformer
{
    public function __construct()
    {
        $this->setDataMap([
            'aid' => ['type' => 'int'],
            'qid' => ['type' => 'int'],
            'code' => true,
            'sortOrder' => ['key' => 'sortorder', 'type' => 'int'],
            'assessmentValue' => ['key' => 'assessment_value', 'type' => 'int'],
            'scaleId' => ['key' => 'scale_id', 'type' => 'int']
        ]);
    }
}
