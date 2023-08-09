<?php

use LimeSurvey\Datavalueobjects\GeneralOption;
use LimeSurvey\Datavalueobjects\FormElement;
use LimeSurvey\Datavalueobjects\SwitchOption;

class MandatoryGeneralOption extends GeneralOption
{
    /**
     * @param Question $question
     */
    public function __construct(Question $question)
    {
        $this->name = 'mandatory';
        $this->title = gT('Mandatory');
        $this->inputType = 'buttongroup';
        $this->formElement = new FormElement(
            'mandatory',
            null,
            gT('Makes this question mandatory in your survey. Option "Soft" gives a possibility to skip a question without giving any answer.', 'unescaped'),
            $question->mandatory,
            [
                'classes' => [],
                'options' => [
                    new SwitchOption(gt('On'), 'Y'),
                    new SwitchOption(gt('Soft'), 'S'),
                    new SwitchOption(gt('Off'), 'N')
                ]
            ]
        );
    }
}
