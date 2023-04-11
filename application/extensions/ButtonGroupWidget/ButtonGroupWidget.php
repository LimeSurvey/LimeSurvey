<?php

/**
 * Creates a Buttongroup that behaves like a switch with radio input type
 */
class ButtonGroupWidget extends CInputWidget
{
    /**
     * @var array Available buttons as value=>caption array
     */
    public $selectOptions = [];

    /** @var string aria label for btn-group */
    public $ariaLabel = '';

    /** @var array html options */
    public $htmlOptions = [];

    /** @var array the value that is currently checked/selected */
    public $checkedOption = '';

    /** Initializes the widget */
    public function init()
    {
        $this->registerClientScript();
    }

    /** Executes the widget */
    public function run()
    {
        $this->renderButtons();
    }

    /** Renders the button group */
    public function renderButtons()
    {
        list($name, $id) = $this->resolveNameID();
        $this->render('buttongroup', [
            'ariaLabel' => $this->ariaLabel,
            'name' => $name,
            'id' => $id,
            'selectOptions' => $this->selectOptions,
            'checkedOption' => $this->checkedOption,
            'htmlOptions' => $this->htmlOptions
        ]);
    }


    /** Registers required script files */
    public function registerClientScript()
    {
    }
}
