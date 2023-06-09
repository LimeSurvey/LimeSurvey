<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputAnswer extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $this->setDataMap([
            'aid' => ['type' => 'int'],
            'qid' => ['type' => 'int'],
            'code' => true,
            'sortorder' => ['key' => 'sortOrder', 'type' => 'int'],
            'assessment_value' => ['key' => 'assessmentValue', 'type' => 'int'],
            'scale_id' => ['key' => 'scaleId', 'type' => 'int']
        ]);
    }

    public function transformAll($collection)
    {
        $collection = parent::transformAll($collection);

        usort(
            $collection,
            function ($a, $b) {
                return (int)(
                    (int)$a['sortOrder'] > (int)$b['sortOrder']
                );
            }
        );

        $output = [];
        foreach ($collection as $value) {
            $output[$value['aid']] = $value;
        }

        return (object) $output;
    }
}
