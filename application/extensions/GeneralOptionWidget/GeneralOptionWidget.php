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

        //workaround if inputType is text, then take out "" in the middle of the string and replace every " inside the string
        //with '
        if($this->generalOption->inputType === 'text'){
            $this->generalOption->formElement->value = str_replace('"', "'",$this->generalOption->formElement->value);
        }

        $content = $this->render($this->generalOption->inputType, null, true);
        $this->render('layout', ['content' => $content]);
    }
}
