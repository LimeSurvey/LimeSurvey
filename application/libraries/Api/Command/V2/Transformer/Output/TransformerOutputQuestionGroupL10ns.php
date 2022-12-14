<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputQuestionGroupL10ns extends TransformerOutputActiveRecord
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
