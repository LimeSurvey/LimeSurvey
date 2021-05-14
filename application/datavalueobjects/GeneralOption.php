<?php

namespace LimeSurvey\Datavalueobjects;

use Survey;

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

    /** @var bool If the Option should be disabled when the survey is active*/
    public $disableInActive = false;

    /** @var bool If the Option should be disabled*/
    public $disabled = false;

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
     * @param Survey $survey
     * @return void
     */
    public function setDisableInActive(Survey $survey)
    {
        $this->disableInActive = true;
        if ($survey->active === 'Y') {
            $this->disabled = true;
        }
    }
}
