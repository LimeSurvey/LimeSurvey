<?php

namespace LimeSurvey\Datavalueobjects;

class SwitchOptions
{
    /** @var SwitchOption[] */
    public $options;

    /**
     * @param SwitchOption[] $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }
}
