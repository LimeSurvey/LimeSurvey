<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Output;

use LimeSurvey\Api\Transformer\{
    Output\TransformerOutputActiveRecord
};

class TransformerOutputQuestion extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $this->setDataMap([
            'qid' => ['type' => 'int'],
            'parent_qid' => ['key' => 'parentQid', 'type' => 'int'],
            'sid' => ['type' => 'int'],
            'type' => true,
            'title' => true,
            'preg' => true,
            'other' => ['formatter' => ['ynToBool' => true]],
            'mandatory' => ['formatter' => ['mandatory' => true]],
            'encrypted' => ['formatter' => ['ynToBool' => true]],
            'question_order' => ['key' => 'sortOrder', 'type' => 'int'],
            'scale_id' => ['key' => 'scaleId', 'type' => 'int'],
            'same_default' => [
                'key' => 'sameDefault',
                'formatter' => ['intToBool' => true]
            ],
            'question_theme_name' => 'questionThemeName',
            'modulename' => 'moduleName',
            'gid' => ['type' => 'int'],
            'relevance' => true,
            'same_script' => [
                'key' => 'sameScript',
                'formatter' => ['intToBool' => true]
            ]
        ]);
    }

    public function transformAll($collection, $options = [])
    {
        $collection = parent::transformAll($collection, $options);

        usort(
            $collection,
            function ($a, $b) {
                return (int)(
                    (int)$a['questionOrder'] > (int)$b['questionOrder']
                );
            }
        );

        return $collection;
    }
}
