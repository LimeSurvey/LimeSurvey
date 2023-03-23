<?php

namespace LimeSurvey\Libraries\FormExtension\Input;

use LimeSurvey\Libraries\FormExtension\Renderer\ButtonGroupInputRenderer;

class ButtonGroupInput extends BaseInput
{
    protected $options = [];

    public function __construct(array $options)
    {
        parent::__construct($options);
        $this->setRenderer(new ButtonGroupInputRenderer);

        $this->options = !empty($options['options']) ? $options['options'] : [];
    }

    public function getOptions()
    {
        return $this->options;
    }
}
