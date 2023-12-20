<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\{
    Transformer,
    TransformerException
};

class TransformerInputQuestionL10ns extends Transformer
{
    public function __construct()
    {
        $this->setDataMap([
            'id' => ['type' => 'int'],
            'qid' => ['type' => 'int', 'required' => 'create'],
            'question' => ['required' => 'create'],
            'help' => true,
            'script' => true,
            'language' => ['required' => true]
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
