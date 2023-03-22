<?php

namespace LimeSurvey\Libraries\FormExtension\Input;

/**
 */
class FileInput extends BaseInput
{
    /** @var array */
    private $accept = [];

    public function __construct(array $options)
    {
        $this->accept = $options['accept'] ?? [];
        $options['attributes'] = array_merge(
            ['type' => 'file', 'accept' => $this->accept],
            $options['attributes'] ?? []
        );
        parent::__construct($options);
    }
}
