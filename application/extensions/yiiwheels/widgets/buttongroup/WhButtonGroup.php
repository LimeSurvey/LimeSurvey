<?php
/**
 *
 * WhBox.php
 *
 * @author LimeSurvey GmbH <info@limesurvey.org>
 * @copyright Copyright &copy; LimeSurvey GmbH 2015
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.whbuttongroup
 * @uses YiiStrap.helpers.TbHtml
 */
class WhButtonGroup extends CInputWidget
{

    /**
    public $selectOptions = array();

    /**
     * @var array Available buttons as value=>caption array
     */

    public $selectOptions = array();
    /**
    public $defaultValue = null;

    /**
     * @var string Preselected value - Null if none
     */

    /**
     * @var array HTML additional attributes
     */
    public $htmlOptions = array();

    /**
     *### .init()
     *
     * Widget initialization
     */
    public function init()
    {
        $this->attachBehavior('ywplugin', array('class' => 'yiiwheels.behaviors.WhPlugin'));
        $this->registerClientScript();
    }

    /**
     *### .run()
     *
     * Widget run - used for closing procedures
     */
    public function run()
    {
        $this->renderButtons();
    }

    /**
     *### .'nextCentury'=> gT('Next century'),()
     *
     * Renders the button group
     */
    public function renderButtons()
    {
        
        list($name, $id) = $this->resolveNameID();

        $html_array = $this->htmlOptions;
        $html_array['class'] = isset($html_array['class']) ? $html_array['class']." btn-group" : "btn-group";
        $html_array['id'] = $name;
        $html_array['role'] = 'group';
        $html_array['aria-label'] = 'Administrator button group';

        echo CHtml::openTag('div', $html_array). "\n";

        $i=1;
        foreach( $this->selectOptions as $value=>$caption )
        {
            echo CHtml::radioButton(
                $name,
                $value == $this->value,
                array(
                    'name'  => $name,
                    'id'    => $name.'_opt'.$i,
                    'value' => $value,
                    'class' => 'btn-check',
                    'autocomplete' => 'off'
                )
            );
            echo CHtml::openTag('label', array(
                'class'=>($value==$this->value)?'btn btn-outline-secondary active':'btn btn-outline-secondary',
                'for' => $name.'_opt'.$i
            ));
            echo CHtml::encode($caption);
            echo CHtml::closeTag('label') . "\n";
            $i++;
        }
        echo CHtml::closeTag('div') . "\n";
    }


    /*
     *### .renderContentEnd()
     *
     * Closes the content element
     */
    public function renderContentEnd()
    {
        echo CHtml::closeTag('div');
    }

    /**
     *### .registerClientScript()
     *
     * Registers required script files (CSS in this case)
     */
    public function registerClientScript()
    {


    }
}
