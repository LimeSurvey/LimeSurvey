<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputSubQuestionAggregate extends Transformer
{
    public function __construct(
        TransformerInputSubQuestion $transformer,
        TransformerInputQuestionL10ns $transformerL10n
    ) {
        $this->setDataMap([
            'question' => [
                'required' => 'create',
                'transformer' => $transformer
            ],
            'questionL10n' => [
                'key' => 'questionI10N',
                'collection' => true,
                'required' => 'create',
                'transformer' => $transformerL10n
            ]
        ]);
    }
}
