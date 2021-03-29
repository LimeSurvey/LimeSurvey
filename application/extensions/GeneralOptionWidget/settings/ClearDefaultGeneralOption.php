<?php

use LimeSurvey\Datavalueobjects\GeneralOption;
use LimeSurvey\Datavalueobjects\FormElement;
use LimeSurvey\Datavalueobjects\SwitchOption;

class ClearDefaultGeneralOption extends GeneralOption
{
    public function __construct()
    {
        $this->name = 'clear_default';
        $this->title = gT('Clear default values');
        $this->inputType = 'switch';
        $this->formElement = new FormElement(
            'clear_default',
            null,
            gT('Default attribute values for this question type will be cleared'),
            '',
            [
                'classes' => [],
                'options' => [
                    new SwitchOption(gt('Off'), 'N'),
                    new SwitchOption(gt('On'), 'Y'),
                ]
            ]
        );
    }
}
