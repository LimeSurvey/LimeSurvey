<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputQuestionAggregate extends Transformer
{
    public function __construct()
    {
        $this->setDataMap([
            'question' => true,
            'questionL10n' => 'questionI10N',
            'attributes' => 'advancedSettings',
            'answers' => 'answeroptions',
            'subquestions' => true
        ]);
    }
}
