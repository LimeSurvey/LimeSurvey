<?php

namespace LimeSurvey\Libraries\FormExtension\Input;

/**
 * Basic text input. No customized behaviour compared to base class.
 */
class TextInput extends BaseInput
{
    public function __construct(array $options)
    {
        $options['attributes'] = array(['type' => 'text'], $options['attributes'] ?? []);
        parent::__construct($options);
    }
}
