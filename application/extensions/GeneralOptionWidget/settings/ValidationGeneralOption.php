<?php

use LimeSurvey\Datavalueobjects\GeneralOption;
use LimeSurvey\Datavalueobjects\FormElement;
use LimeSurvey\Datavalueobjects\SwitchOption;

class ValidationGeneralOption extends GeneralOption
{
    /**
     * @param Question $question
     */
    public function __construct(Question $question)
    {
        $this->name = 'preg';
        $this->title = gT('Input validation');
        $this->inputType = 'text';
        $this->formElement = new FormElement(
            'preg',
            null,
            gT('You can add any regular expression based validation in here','unescaped'),
            $question->preg,
            [
                'classes' => ['form-control'],
                'inputGroup' => [
                    'prefix' => 'RegExp',
                ]
            ]
        );
    }
}
