<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputQuestionGroupL10ns extends Transformer
{
    public function __construct()
    {
        $this->setDataMap([
            'id' => ['type' => 'int'],
            'gid' => ['type' => 'int'],
            'groupName' => 'group_name',
            'description' => true,
            'language' => true
        ]);
    }
}
