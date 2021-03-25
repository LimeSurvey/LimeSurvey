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

        // If the formElement has extra attributes defined, make sure they don't override the basic attributes
        if (!empty($this->generalOption->formElement->options['attributes'])) {
            unset($this->generalOption->formElement->options['attributes']['id']);
            unset($this->generalOption->formElement->options['attributes']['name']);
            unset($this->generalOption->formElement->options['attributes']['value']);
            unset($this->generalOption->formElement->options['attributes']['class']);
        } else {
            $this->generalOption->formElement->options['attributes'] = [];
        }

        $content = $this->render($this->generalOption->inputType, null, true);
        $this->render('layout', ['content' => $content]);
    }
}
