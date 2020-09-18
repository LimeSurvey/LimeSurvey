<?php

namespace LimeSurvey\Datavalueobjects;

/**
 * Wrapper class for question general option.
 */
class GeneralOption
{
    public function __construct(
        $name,
        $title,
        $inputtype,
        $formElementId,
        $formElementName,
        $formElementHelp,
        $formElementValue,
        $formElementOptions
    ) {
    }

    /**
     * @param string $clear_default
     * @return void
     */
    public function setClearDefault($clear_default)
    {
        $this->clear_default = $clear_default;
    }

    /**
     * @param string $clear_default
     * @return void
     */
    public function setPreg($preg)
    {
        $this->preg = $preg;
    }
}
