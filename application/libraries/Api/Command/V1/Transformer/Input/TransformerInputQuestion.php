<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\{
    Transformer,
    Formatter\FormatterYnToBool
};

class TransformerInputQuestion extends Transformer
{
    public function __construct()
    {
        $formatterYn = new FormatterYnToBool(true);

        $this->setDataMap([
            'qid' => ['type' => 'int'],
            'parentQid' => ['key' => 'parent_qid', 'type' => 'int'],
            'sid' => ['type' => 'int'],
            'type' => true,
            'title' => true,
            'preg' => true,
            'other' => ['formatter' => $formatterYn],
            'mandatory' => ['formatter' => $formatterYn],
            'encrypted' => ['formatter' => $formatterYn],
            'questionOrder' => ['key' => 'question_order', 'type' => 'int'],
            'scaleId' => ['key' => 'scale_id', 'type' => 'int'],
            'sameDefault' => ['key' => 'same_default', 'formatter' => $formatterYn],
            'questionThemeName' => 'question_theme_name',
            'moduleName' => 'modulename',
            'gid' => ['type' => 'int'],
            'relevance' => true,
            'sameScript' => ['key' => 'same_script', 'formatter' => $formatterYn]
        ]);
    }
}
