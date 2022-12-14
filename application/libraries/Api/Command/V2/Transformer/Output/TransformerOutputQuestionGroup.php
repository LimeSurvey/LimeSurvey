<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputQuestionGroup extends TransformerOutputActiveRecord
{
    public function transformAll($array)
    {
        $array = parent::transformAll($array);

        usort($array, function ($a, $b) {
            return ((int)$a['group_order']) > ((int)$b['group_order']);
        });

        return $array;
    }

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
