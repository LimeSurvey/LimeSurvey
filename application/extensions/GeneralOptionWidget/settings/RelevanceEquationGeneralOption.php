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
            $content = gT("Note: You can't edit the condition because there are currently conditions set for this question by the condition designer.");
        } else {
            $inputType = 'textarea';
            $content = gT("A condition can be used to add branching logic using ExpressionScript. Either edit it directly here or use the Condition designer.");
        }

        $this->name = 'relevance';
        $this->title = gT('Condition');
        $this->inputType = $inputType;
        $this->formElement = new FormElement(
            'relevance',
            null,
            $content,
            $question->relevance,
            [
                'classes' => ['form-control'],
                'attributes' => [
                    'rows' => 1,
                    'readonly' => count($question->conditions) > 0
                ],
                'inputGroup' => [
                    'prefix' => '{',
                    'suffix' => '}'
                ]
            ]
        );
    }
}
