<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputQuestion extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $typeYnToBool = 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool';

        $this->setDataMap([
            'qid' => ['type' => 'int'],
            'parent_qid' => ['type' => 'int'],
            'sid' => ['type' => 'int'],
            'type' => true,
            'title' => true,
            'preg' => true,
            'other' => ['type' => $typeYnToBool],
            'mandatory' => ['type' => $typeYnToBool],
            'encrypted' => ['type' => $typeYnToBool],
            'question_order' => ['type' => 'int'],
            'scale_id' => ['type' => 'int'],
            'same_default' => ['type' => $typeYnToBool],
            'question_theme_name' => true,
            'modulename' => true,
            'gid' => ['type' => 'int'],
            'relevance' => true,
            'same_script' => ['type' => $typeYnToBool]
        ]);
    }

    public function transformAll($array)
    {
        $array = parent::transformAll($array);

        usort($array,function($a, $b){
            return ((int)$a['question_order']) > ((int)$b['question_order']);
        });

        return $array;
    }
}
