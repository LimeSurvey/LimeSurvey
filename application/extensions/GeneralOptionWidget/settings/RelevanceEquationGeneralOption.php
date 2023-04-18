<?php

use LimeSurvey\Datavalueobjects\GeneralOption;
use LimeSurvey\Datavalueobjects\FormElement;

class RelevanceEquationGeneralOption extends GeneralOption
{
    /**
     * @param Question $question
     */
    public function __construct(Question $question)
    {
        if (count($question->conditions) > 0) {
            $inputType = 'text';
            $help = gT("Note: If you customize & save this condition you will not be able to use the condition editor for this, anymore.", 'unescaped');
        } else {
            $inputType = 'textarea';
            $help = gT("A condition can be used to add branching logic using ExpressionScript. Either edit it directly here or use the Condition designer.", 'unescaped');
        }

        $this->name = 'relevance';
        $this->title = gT('Condition');
        $this->inputType = $inputType;
        $this->formElement = new FormElement(
            'relevance',
            null,
            $help,
            $question->relevance,
            [
                'classes' => ['form-control'],
                'attributes' => [
                    'rows' => 1,
                    'data-has-conditions' => count($question->conditions) > 0
                ],
                'inputGroup' => [
                    'prefix' => '{',
                    'suffix' => '}'
                ]
            ]
        );
    }
}
