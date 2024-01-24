<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputQuestionAttribute extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $this->setDataMap([
            'qaid'      => ['type' => 'int'],
            'attribute' => ['type' => 'int'],
            'value'     => true,
            'language'  => true
        ]);
    }

    /**
     * Transform collection
     *
     * @param array $collection
     * @param ?array $options
     * @return array
     */
    public function transformAll($collection, $options = [])
    {
        $attributes = (object) $collection;
        $tfAttributes = [];
        foreach ($attributes as $attrSet) {
            if (!array_key_exists($attrSet->attribute, $tfAttributes)) {
                $tfAttributes[$attrSet->attribute] = [
                    'qid' => (int) $attrSet->qid,
                    $attrSet->language => [
                        'qaid'  => (int) $attrSet->qaid,
                        'value' => $attrSet->value
                    ]
                ];
            } else {
                $tfAttributes[$attrSet->attribute][$attrSet->language] = [
                    'qaid'  => (int) $attrSet->qaid,
                    'value' => $attrSet->value
                ];
            }
        }
        return $tfAttributes;
    }
}
