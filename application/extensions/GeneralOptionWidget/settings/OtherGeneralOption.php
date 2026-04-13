<?php

use LimeSurvey\Datavalueobjects\GeneralOption;
use LimeSurvey\Datavalueobjects\FormElement;
use LimeSurvey\Datavalueobjects\SwitchOption;

class OtherGeneralOption extends GeneralOption
{
    /**
     * @param Question $question
     */
    public function __construct(Question $question)
    {
        $this->name = 'other';
        $this->title = gT('Other');
        $this->inputType = 'switch';
        $this->setDisableInActive($question->survey);
        $this->formElement = new FormElement(
            'other',
            null,
            gT('Activate the "other" option for your question'),
            $question->other,
            [
                'classes' => [],
                'options' => [
                    new SwitchOption(gT('Off'), 'N'),
                    new SwitchOption(gT('On'), 'Y')
                ]
            ]
        );
    }
}
