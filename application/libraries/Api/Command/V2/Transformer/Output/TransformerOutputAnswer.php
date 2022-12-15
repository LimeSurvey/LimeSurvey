<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputAnswer extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $this->setDataMap([
            'aid' => ['type' => 'int'],
            'qid' => ['type' => 'int'],
            'code' => true,
            'sortorder' => ['type' => 'int'],
            'assessment_value' => ['type' => 'int'],
            'scale_id' => ['type' => 'int']
        ]);
    }

    public function transformAll($array)
    {
        $array = parent::transformAll($array);

        usort($array,function ($a, $b){
            return ((int)$a['sortorder']) > ((int)$b['sortorder']);
        });

        return $array;
    }
}
