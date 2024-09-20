<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Output;

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

    public function transformAll($collection, $options = [])
    {
        $collection = parent::transformAll($collection, $options);

        usort(
            $collection,
            function ($a, $b) {
                return (int)(
                    (int)$a['groupOrder'] > (int)$b['groupOrder']
                );
            }
        );

        return $collection;
    }
}
