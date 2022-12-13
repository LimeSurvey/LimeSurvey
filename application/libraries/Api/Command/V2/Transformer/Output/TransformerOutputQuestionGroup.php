<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

class TransformerOutputQuestionGroup extends TransformerOutputActiveRecordAbstract
{
    protected function getDataMap()
    {
        return [
            'gid' => true,
            'sid' => true,
            'group_order' => true,
            'randomization_group' => true,
            'grelevance' => true
        ];
    }
}
