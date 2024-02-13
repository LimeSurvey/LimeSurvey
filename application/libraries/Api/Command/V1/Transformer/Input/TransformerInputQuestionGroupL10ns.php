<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Registry\ValidationRegistry;
use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputQuestionGroupL10ns extends Transformer
{
    public function __construct(ValidationRegistry $validationRegistry)
    {
        $this->setRegistry($validationRegistry);
        $this->setDataMap([
            'id' => ['type' => 'int'],
            'gid' => ['type' => 'int'],
            'groupName' => ['key' => 'group_name', 'required' => 'create'],
            'description' => true,
            'language' => true
        ]);
    }
}
