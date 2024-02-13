<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\{Registry\ValidationRegistry, Transformer};

class TransformerInputQuestionL10ns extends Transformer
{
    public function __construct(ValidationRegistry $validationRegistry)
    {
        $this->setRegistry($validationRegistry);
        $this->setDataMap([
            'id' => ['type' => 'int'],
            'qid' => ['type' => 'int'],
            'question' => ['required' => 'create'],
            'help' => true,
            'script' => true,
            'language' => true
        ]);
    }

    public function validate($data, $options = [])
    {
        if (empty($data)) {
            return ['Data can not be empty'];
        } elseif (is_scalar($data)) {
            return ['Can not be scalar'];
        }
        return parent::validate($data, $options);
    }

    public function validateAll($collection, $options = [])
    {
        if (empty($collection)) {
            return ['Collection can not be empty'];
        }
        return parent::validateAll($collection, $options);
    }
}
