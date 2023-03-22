<?php

namespace LimeSurvey\Libraries\FormExtension\Input;

use LimeSurvey\Libraries\FormExtension\Renderer\ButtonSwitchInputRenderer;

class ButtonSwitchInput extends BaseInput
{
    public function __construct(array $options)
    {
        parent::__construct($options);
        $this->setRenderer(new ButtonSwitchInputRenderer);
    }
}
