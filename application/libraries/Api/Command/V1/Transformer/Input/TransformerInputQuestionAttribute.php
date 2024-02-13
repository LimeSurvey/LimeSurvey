<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Registry\ValidationRegistry;
use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputQuestionAttribute extends Transformer
{
    public function __construct(ValidationRegistry $validationRegistry)
    {
        $this->setRegistry($validationRegistry);
    }

    /**
     * Converts the raw array to the expected format.
     */
    public function transformAll($collection, $options = [])
    {
        $attributes = [];
        foreach ($collection as $attrName => $languages) {
            foreach ($languages as $lang => $value) {
                if ($lang !== '') {
                    $attributes[0][$attrName][$lang] = $value;
                } else {
                    $attributes[0][$attrName] = $value;
                }
            }
        }
        return $attributes;
    }
}
