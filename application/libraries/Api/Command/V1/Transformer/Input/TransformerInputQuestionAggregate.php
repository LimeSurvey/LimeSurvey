<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputQuestionAggregate extends Transformer
{
    protected TransformerInputQuestion $transformer;
    protected TransformerInputQuestionL10ns $transformerL10n;
    protected TransformerInputQuestionAttribute $transformerAttribute;
    protected TransformerInputAnswer $transformerAnswer;
    protected TransformerInputAnswerL10ns $transformerAnswerL10n;

    public function __construct(
        TransformerInputQuestion $transformer,
        TransformerInputQuestionL10ns $transformerL10n,
        TransformerInputQuestionAttribute $transformerAttribute,
        TransformerInputAnswer $transformerAnswer,
        TransformerInputAnswerL10ns $transformerAnswerL10n
    )
    {
        $this->transformer = $transformer;
        $this->transformerL10n = $transformerL10n;
        $this->transformerAttribute = $transformerAttribute;
        $this->transformerAnswer = $transformerAnswer;
        $this->transformerAnswerL10n = $transformerAnswerL10n;

        $this->setDataMap([
            'question' => [
                'required' => true,
                'transformer' => $this->transformer
            ],
            'questionL10n' => [
                'key' => 'questionI10N',
                'collection' => true,
                'required' => true,
                'transformer' => $this->transformerL10n
            ],
            'attributes' => [
                'key' => 'advancedSettings',
                'collection' => true,
                'transformer' => $this->transformerAttribute
            ],
            'answers' => [
                'key' => 'answeroptions',
                'collection' => true,
                'transformer' => $this->transformerAnswer
            ],
            'subquestions' => [
                'collection' => true,
                'transformer' => $this->transformer
            ],
        ]);
    }
}
