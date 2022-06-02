<?php

class GeneralOptionWidget extends CWidget
{
    /** @var GeneralOption */
    public $generalOption;

    const SINGLEINPUTTYPE = array(
        'questiongroup',
        'questiontheme',
        'text',
        'textarea'
    );

    /**
     * @todo Classes instead of switch.
     */
    public function run()
    {
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
