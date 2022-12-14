<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputAnswer extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $this->setDataMap([
            'aid' => true,
            'qid' => true,
            'code' => true,
            'sortorder' => true,
            'assessment_value' => true,
            'scale_id' => true
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
