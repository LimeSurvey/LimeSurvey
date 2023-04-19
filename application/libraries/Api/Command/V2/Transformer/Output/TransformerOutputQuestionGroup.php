<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputQuestionGroup extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $this->setDataMap([
            'gid' => ['type' => 'int'],
            'sid' => ['type' => 'int'],
            'group_order' => ['key' => 'groupOrder', 'type' => 'int'],
            'randomization_group' => 'randomizationGroup',
            'grelevance' => 'gRelevance',
        ]);
    }

    public function transformAll($array)
    {
        $array = parent::transformAll($array);

        usort($array, function ($a, $b) {
            return ((int)$a['groupOrder']) > ((int)$b['groupOrder']);
        });

        return $array;
    }
}
