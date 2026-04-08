<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputQuestionAttribute extends TransformerOutputActiveRecord
{
    /**
     * Transform collection
     *
     * @param array $collection
     * @param ?array $options
     * @return array
     */
    public function transformAll($collection, $options = [])
    {
        $attributes = [];
        foreach ($collection as $attr) {
            if (!array_key_exists($attr->attribute, $attributes)) {
                $attributes[$attr->attribute] = [];
            }
            if (!array_key_exists($attr->language, $attributes[$attr->attribute])) {
                $attributes[$attr->attribute][$attr->language] = '';
            }
            $attributes[$attr->attribute][$attr->language] = $attr->value;
        }
        return $attributes;
    }
}
