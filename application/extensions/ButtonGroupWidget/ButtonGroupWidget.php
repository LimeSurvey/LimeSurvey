<?php

/**
 * Creates a Buttongroup that behaves like a switch with radio input type
 */
class ButtonGroup extends CInputWidget
{
    /**
     * @var array Available buttons as value=>caption array
     */
    public $selectOptions = array();

    /** @var array html options */
    public $htmlOptions = array();

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

        $html_array = $this->htmlOptions;
        $html_array['class'] = isset($html_array['class']) ? $html_array['class']." btn-group" : "btn-group";
        $html_array['id'] = $id;
        $html_array['role'] = 'group';
        $html_array['aria-label'] = 'Administrator button group';

        echo CHtml::openTag('div', $html_array). "\n";

        $i=1;
        foreach( $this->selectOptions as $checkedValue=>$caption )
        {
            echo CHtml::radioButton(
                $name,
                $checkedValue == $this->value,
                array(
                    'name'  => $name,
                    'id'    => $name.'_opt'.$i,
                    'value' => $checkedValue,
                    'class' => 'btn-check',
                    'autocomplete' => 'off'
                )
            );
            echo CHtml::openTag('label', array(
                'class'=>($checkedValue==$this->value)?'btn btn-outline-secondary active':'btn btn-outline-secondary',
                'for' => $name.'_opt'.$i
            ));
            echo CHtml::encode($caption);
            echo CHtml::closeTag('label') . "\n";
            $i++;
        }
        echo CHtml::closeTag('div') . "\n";
    }


    /** Registers required script files */
    public function registerClientScript()
    {

    }
}
