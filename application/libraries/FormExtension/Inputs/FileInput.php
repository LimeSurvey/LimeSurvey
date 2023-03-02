<?php

namespace LimeSurvey\Libraries\FormExtension\Inputs;

/**
 */
class FileInput extends BaseInput
{
    /** @var array */
    private $accept = [];

    public function __construct(array $options)
    {
        parent::__construct($options);

        $this->accept = $options['accept'] ?? [];
    }

    public function getAccept(): array
    {
        return $this->accept;
    }
}
