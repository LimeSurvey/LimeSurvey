<?php

class GeneralOptionWidget extends CWidget
{
    /** @var GeneralOption */
    public $generalOption;

    /**
     * @todo Classes instead of switch.
     */
    public function run()
    {
        $this->render($this->generalOption->inputType);
    }
}
