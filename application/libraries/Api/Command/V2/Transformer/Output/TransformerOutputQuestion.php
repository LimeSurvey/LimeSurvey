<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

class TransformerOutputQuestion extends TransformerOutputActiveRecordAbstract
{
    protected function getDataMap()
    {
        return [
            'qid' => true,
            'parent_qid' => true,
            'sid' => true,
            'type' => true,
            'title' => true,
            'preg' => true,
            'other' => true,
            'mandatory' => true,
            'encrypted' => true,
            'question_order' => true,
            'scale_id' => true,
            'same_default' => true,
            'question_theme_name' => true,
            'modulename' => true,
            'gid' => true,
            'relevance' => true,
            'same_script' => true,
        ];
    }
}
