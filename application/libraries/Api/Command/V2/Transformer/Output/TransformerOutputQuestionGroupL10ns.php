<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecordAbstract;

class TransformerOutputQuestionGroupL10ns extends TransformerOutputActiveRecordAbstract
{
    protected function getDataMap()
    {
        return [
            'id' => true,
            'gid' => true,
            'group_name' => true,
            'description' => true,
            'language' => true
        ];
    }
}
