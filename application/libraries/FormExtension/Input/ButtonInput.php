<?php

namespace LimeSurvey\Libraries\FormExtension\Input;

/**
 * Button input.
 */
class ButtonInput extends BaseInput
{
    public function __construct(array $options)
    {
        $options = array_merge(
            ['html_tag' => 'button'],
            $options ?? []
        );
        $options['attributes'] = array_merge(
            [
                'type' => 'button',
                'class' => 'btn btn-secondary'
            ],
            $options['attributes'] ?? []
        );
        parent::__construct($options);
    }
}
