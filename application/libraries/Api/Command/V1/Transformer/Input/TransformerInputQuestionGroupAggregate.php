<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputQuestionGroupAggregate extends Transformer
{
    public function __construct(
        TransformerInputQuestionGroup $transformerQuestionGroup,
        TransformerInputQuestionGroupL10ns $transformerQuestionGroupL10ns
    ) {
        $this->setDataMap([
            'questionGroup' => [
                'transformer' => $transformerQuestionGroup,
                'required'
            ],
            'questionGroupL10n' => [
                'key' => 'questionGroupI10N',
                'collection' => true,
                'transformer' => $transformerQuestionGroupL10ns,
                'required'
            ]
        ]);
    }
}
