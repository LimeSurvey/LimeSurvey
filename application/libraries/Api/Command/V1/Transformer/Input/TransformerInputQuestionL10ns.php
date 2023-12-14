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

    public function transform($data, $options = [])
    {
        if (empty($data)) {
            throw new TransformerException('Data can not be empty');
        }
        return parent::transform($data, $options);
    }

    public function transformAll($collection, $options = [])
    {
        if (empty($collection)) {
            throw new TransformerException('Collection can not be empty');
        }
        return parent::transformAll($collection, $options);
    }
}
