<?php

namespace LimeSurvey\Datavalueobjects;

/**
 * @todo Is this class needed, or can it be included in widget DVOs?
 * @todo $name is always null, always === $id.
 */
class FormElement
{
    /** @var string */
    public $id;

    /** @var string|null */
    public $name;

    /** @var string */
    public $help;

    /** @var mixed */
    public $value;

    /** @var array */
    public $options;

    /**
     * @param string $id
     * $param string|null $name Null if same as id
     * @param string $help
     * @param mixed $value
     * @param array $options
     */
    public function __construct(
        $id,
        $name,
        $help,
        $value,
        $options
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->help = $help;
        $this->value = $value;
        $this->options = $options;
    }
}
