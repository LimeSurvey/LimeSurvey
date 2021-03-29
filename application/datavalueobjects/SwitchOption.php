<?php

namespace LimeSurvey\Datavalueobjects;

class SwitchOption
{
    /** @var string */
    public $text;

    /** @var mixed */
    public $value;

    /**
     * @param string $text
     * @param mixed $value
     */
    public function __construct($text, $value)
    {
        $this->text = $text;
        $this->value = $value;
    }
}
