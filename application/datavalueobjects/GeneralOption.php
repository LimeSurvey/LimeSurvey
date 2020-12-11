<?php

namespace LimeSurvey\Datavalueobjects;

/**
 * Wrapper class for question general option.
 */
class GeneralOption
{
    /** @var string */
    public $name;

    /** @var string */
    public $title;

    /** @var string */
    public $inputType;

    /** @var FormElement */
    public $formElement;

    /** @var bool */
    public $disableInActive = false;

    public function __construct(
        $name,
        $title,
        $inputType,
        $formElement
    ) {
        $this->name = $name;
        $this->title = $title;
        $this->inputType = $inputType;
        $this->formElement = $formElement;
    }

    /**
     * @return void
     */
    public function setDisableInActive()
    {
        $this->disableInActive = true;
    }
}
