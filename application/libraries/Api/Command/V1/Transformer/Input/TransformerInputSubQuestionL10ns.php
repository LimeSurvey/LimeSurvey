<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputSubQuestionL10ns extends Transformer
{
    public function __construct()
    {
        $this->setDataMap([
            'question' => ['required'],
        ]);
    }

    public function transform($data, $options = [])
    {
        $question = '';
        $transformed = parent::transform(
            $data,
            $options
        );
        if (is_array($transformed)) {
            $question = array_key_exists(
                'question',
                $transformed
            ) ? $transformed['question'] : '';
        }
        return $question;
    }
}
