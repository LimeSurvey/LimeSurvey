<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Output;

use LimeSurvey\Api\Transformer\{
    Output\TransformerOutputActiveRecord,
    Formatter\FormatterYnToBool
};

class TransformerOutputQuestion extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $formatterYn = new FormatterYnToBool();

        $this->setDataMap([
            'qid' => ['type' => 'int'],
            'parent_qid' => ['key' => 'parentQid', 'type' => 'int'],
            'sid' => ['type' => 'int'],
            'type' => true,
            'title' => true,
            'preg' => true,
            'other' => ['formatter' => $formatterYn],
            'mandatory' => ['formatter' => $formatterYn],
            'encrypted' => ['formatter' => $formatterYn],
            'question_order' => ['key' => 'questionOrder', 'type' => 'int'],
            'scale_id' => ['key' => 'scaleId', 'type' => 'int'],
            'same_default' => ['key' => 'sameDefault', 'formatter' => $formatterYn],
            'question_theme_name' => 'questionThemeName',
            'modulename' => 'moduleName',
            'gid' => ['type' => 'int'],
            'relevance' => true,
            'same_script' => ['key' => 'sameScript', 'formatter' => $formatterYn]
        ]);
    }

    public function transformAll($collection)
    {
        $collection = parent::transformAll($collection);

        usort(
            $collection,
            function ($a, $b) {
                return (int)(
                    (int)$a['questionOrder'] > (int)$b['questionOrder']
                );
            }
        );

        $output = [];
        foreach ($collection as $value) {
            $output[$value['qid']] = $value;
        }

        return (object) $collection;
    }
}
