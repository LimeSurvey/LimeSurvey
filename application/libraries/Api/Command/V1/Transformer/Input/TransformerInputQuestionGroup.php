<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputQuestionGroup extends Transformer
{
    public function __construct()
    {
        $this->setDataMap([
            'gid' => ['type' => 'int'],
            'sid' => ['type' => 'int'],
            'groupOrder' => ['key' => 'group_order', 'type' => 'int'],
            'randomizationGroup' => 'randomization_group',
            'gRelevance' => 'grelevance'
        ]);
    }
}
