<?php
/**
 * TbForm class file.
 *
 * Support for Yii formbuilder
 * @link http://www.yiiframework.com/doc/guide/1.1/en/form.builder
 *
 * Usage:
 *
 * 1. Create a CForm model
 *


class FormbuilderTestModel extends CFormModel
{
    public $search;
    public $agree;
    public $radiolist;

    public function rules()
    {
        return array(
            array('search', 'required'),
            array('agree,radiolist', 'boolean'),
            array('agree', 'compare', 'compareValue' => true,
                'message' => 'You must agree...'),

        );
    }

    // Change the labels here
    public function attributeLabels()
    {
        return array(
            'search'=>'Text search',
            'selectlist'=>'I agree',
        );
    }

    // return the formbuilder config
    public function getFormConfig()
    {
        array(
            'title' => 'Formbuilder test form',
            'showErrorSummary' => true,
            'elements' => array(
                'search' => array(
                    'type' => 'text',
                    'maxlength' => 32,
                    'hint' => 'This is a hint',
                    'placeholder' => 'title',
                    'class' => 'input-large',
                    'append' => '<i class="icon-search"></i>',
                    ),
                'agree' => array(
                    'type' => 'checkbox',
                  // 'hint' => 'Agree to terms and conditions',
                ),

                'radiolist' => array(
                    'type' => 'radiolist',
                    'items' => array('item1' => '1', 'item2' => '2', 'item3' => '3'),
                ),
                'buttons' => array(
                    'submit' => array(
                        'type' => 'submit', //@see TbFormButtonElement::$TbButtonTypes
                        'layoutType' => 'primary', //@see TbButton->type
                        'label' => 'Submit',
                    ),
                    'reset' => array(
                        'type' => 'reset',
                        'label' => 'Reset',
                    ),
                ),
            )
        );
    }

 *
* 2. Create a testaction in the controller
*
* Check TbFormInputElement::$tbActiveFormMethods for available types
*
	public function actionFormbuilderTest()
	{
	        $model = new FormbuilderTestModel;

	        if(isset($_POST['FormbuilderTestModel']))
	        $model->attributes = $_POST['FormbuilderTestModel'];

	        $model->validate();

	        $form = TbForm::createForm($model->getFormConfig(),$model,
	                    array( //@see TbActiveForm attributes
	                        'htmlOptions'=>array('class'=>'well'),
	                        'type'=>'horizontal', //'inline','horizontal','vertical'
	                        ...
	                    )
	                );

	        //no need for an extra view file for testing
	        $this->renderText($form);
	        //$this->render('formbuildertest',array('form'=>$form);
}
*
*
*
* @author Joe Blocher <yii@myticket.at>
* @copyright Copyright &copy; Joe Blocher 2012
* @license http://www.opensource.org/licenses/bsd-license.php New BSD License
* @package bootstrap.widgets
*/

Yii::import('bootstrap.widgets.*');

class TbForm extends CForm
{
    /**
     * @var string the name of the class for representing a form input element. Defaults to 'TbFormInputElement'.
     */
    public $inputElementClass = 'TbFormInputElement';

    /**
     * @var string the name of the class for representing a form button element. Defaults to 'CFormButtonElement'.
     */
    public $buttonElementClass = 'TbFormButtonElement';

    /**
     * Create the TbForm and assign the TbActiveForm with options as activeForm
     *
     * @param $config
     * @param $parent
     * @param array $options
     * @return mixed
     */
    public static function createForm($config, $parent, $options = array())
    {
        $class = __CLASS__;
        $options['class'] = 'TbActiveForm';

        $form = new $class($config, $parent);
        $form->activeForm = $options;

        return $form;
    }

    /**
     * Override parent
     * Remove wrapper with class="row ..."
     *
     * @param mixed $element
     * @return string
     */
    public function renderElement($element)
    {
        if ($element instanceof TbFormInputElement)
        {
	        if ($element->type === 'hidden')
		        return "<div style=\"display:none\">\n".$element->renderInput()."</div>\n";
	        else
		        return $element->render();
        }

        return parent::renderElement($element);
    }

    /**
     * Render the buttons as TbFormButtonElement
     *
     * @return string
     */
    public function renderButtons()
    {
        $output = '';
        foreach ($this->getButtons() as $button)
        {
            $output .= $this->renderElement($button) . '&nbsp;';
        }

        //form-actions div wrapper only if not is inline form
        if ($output !== '' && $this->getActiveFormWidget()->type !== 'inline')
            $output = "<div class=\"form-actions\">\n" . $output . "</div>\n";

        return $output;
    }

}
