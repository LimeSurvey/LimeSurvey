<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputQuestion extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $ynToBool = function ($value) {
            return strtolower($value) === 'y';
        };

        $this->setDataMap([
            'qid' => ['type' => 'int'],
            'parent_qid' => ['type' => 'int'],
            'sid' => ['type' => 'int'],
            'type' => true,
            'title' => true,
            'preg' => true,
            'other' => ['type' => $ynToBool],
            'mandatory' => ['type' => $ynToBool],
            'encrypted' => ['type' => $ynToBool],
            'question_order' => ['type' => 'int'],
            'scale_id' => ['type' => 'int'],
            'same_default' => ['type' => $ynToBool],
            'question_theme_name' => true,
            'modulename' => true,
            'gid' => ['type' => 'int'],
            'relevance' => true,
            'same_script' => ['type' => $ynToBool],
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
