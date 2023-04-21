<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputQuestion extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $typeYnToBool = 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool';

        $this->setDataMap([
            'qid' => ['type' => 'int'],
            'parent_qid' => ['key' => 'parentQid', 'type' => 'int'],
            'sid' => ['type' => 'int'],
            'type' => true,
            'title' => true,
            'preg' => true,
            'other' => ['type' => $typeYnToBool],
            'mandatory' => ['type' => $typeYnToBool],
            'encrypted' => ['type' => $typeYnToBool],
            'question_order' => ['key' => 'questionOrder', 'type' => 'int'],
            'scale_id' => ['key' => 'scaleId', 'type' => 'int'],
            'same_default' => ['key' => 'sameDefault', 'type' => $typeYnToBool],
            'question_theme_name' => 'questionThemeName',
            'modulename' => 'moduleName',
            'gid' => ['type' => 'int'],
            'relevance' => true,
            'same_script' => ['key' => 'sameScript', 'type' => $typeYnToBool]
        ]);
    }

    public function transformAll($array)
    {
        $array = parent::transformAll($array);

        usort($array,function($a, $b){
            return ((int)$a['questionOrder']) > ((int)$b['questionOrder']);
        });

        return $array;
    }
}
