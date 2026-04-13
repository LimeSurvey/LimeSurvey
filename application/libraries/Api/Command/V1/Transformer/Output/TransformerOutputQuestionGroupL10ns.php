<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputQuestionGroupL10ns extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $this->setDataMap([
            'id' => ['type' => 'int'],
            'gid' => ['type' => 'int'],
            'group_name' => 'groupName',
            'description' => true,
            'language' => true
        ]);
    }
}
