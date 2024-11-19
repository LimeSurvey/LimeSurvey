<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputQuestionAggregate extends Transformer
{
    public function __construct(
        TransformerInputQuestion $transformer,
        TransformerInputQuestionL10ns $transformerL10n,
        TransformerInputQuestionAttribute $transformerAttribute,
        TransformerInputAnswer $transformerAnswer,
        TransformerInputSubQuestion $transformerInputSubQuestion
    ) {
        $this->setDataMap([
            'question' => [
                'required',
                'transformer' => $transformer
            ],
            'questionL10n' => [
                'key' => 'questionI10N',
                'collection' => true,
                'required',
                'transformer' => $transformerL10n
            ],
            'attributes' => [
                'key' => 'advancedSettings',
                'collection' => true,
                'transformer' => $transformerAttribute
            ],
            'answers' => [
                'key' => 'answeroptions',
                'collection' => true,
                'transformer' => $transformerAnswer
            ],
            'subquestions' => [
                'collection' => true,
                'transformer' => $transformerInputSubQuestion
            ],
        ]);
    }
}
