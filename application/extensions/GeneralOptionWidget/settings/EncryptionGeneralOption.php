<?php

use LimeSurvey\Datavalueobjects\GeneralOption;
use LimeSurvey\Datavalueobjects\FormElement;
use LimeSurvey\Datavalueobjects\SwitchOption;

class EncryptionGeneralOption extends GeneralOption
{
    /**
     * @param Question $question
     */
    public function __construct(Question $question)
    {
        $this-> name = 'encrypted';
        $this-> title = gT('Encrypted');
        $this->inputType = 'switch';
        $this->formElement = new FormElement(
            'encrypted',
            null,
            gT('Store the answers to this question encrypted'),
            $question->encrypted,
            [
                'classes' => [],
                'options' => [
                    new SwitchOption(gT('Off'), 'N'),
                    new SwitchOption(gT('On'), 'Y'),
                ]
            ]
        );
        $this->setDisableInActive($question->survey);
    }
}
