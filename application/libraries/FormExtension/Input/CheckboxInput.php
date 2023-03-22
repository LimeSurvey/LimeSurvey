<?php

namespace LimeSurvey\Libraries\FormExtension\Input;

/**
 * Basic checkbox input.
 */
class CheckboxInput extends BaseInput
{
    public function __construct(array $options)
    {
        $options['attributes'] = array_merge(
            ['type' => 'checkbox'],
            $options['attributes'] ?? []
        );
        parent::__construct($options);
    }
}
