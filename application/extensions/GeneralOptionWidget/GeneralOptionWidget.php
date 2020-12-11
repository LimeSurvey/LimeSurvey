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
        if ($this->generalOption->inputType === 'buttongroup') {
            //echo '<pre>';print_r($this->generalOption->formElement->options);die;
        }
        $content = $this->render($this->generalOption->inputType, null, true);
        $this->render('layout', ['content' => $content]);
    }
}
