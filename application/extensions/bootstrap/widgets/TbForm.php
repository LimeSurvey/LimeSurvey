<?php

/**
 * TbForm class file.
 *
 * Support for Yii formbuilder
 *
 * @link http://www.yiiframework.com/doc/guide/1.1/en/form.builder
 *
 * Usage:
 *
 * 1. Create a CForm model
 *
 * @author Joe Blocher <yii@myticket.at>
 * @copyright Copyright &copy; Joe Blocher 2012
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
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
     * Create the TbForm and assign the TbActiveForm with options as activeForm.
     *
     * @param $config
     * @param $parent
     * @param array $options
     *
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
     * Remove wrapper with class="row ...".
     *
     * @param mixed $element
     *
     * @return string
     */
    public function renderElement($element)
    {
        if ($element instanceof TbFormInputElement) {
            if ($element->type === 'hidden') {
                return "<div style=\"display:none\">\n".$element->renderInput()."</div>\n";
            } else {
                return $element->render();
            }
        }

        return parent::renderElement($element);
    }

    /**
     * Render the buttons as TbFormButtonElement.
     *
     * @return string
     */
    public function renderButtons()
    {
        $output = '';
        foreach ($this->getButtons() as $button) {
            $output .= $this->renderElement($button).'&nbsp;';
        }

        //form-actions div wrapper only if not is inline form
        if ($output !== '' && $this->getActiveFormWidget()->type !== 'inline') {
            $output = "<div class=\"form-actions\">\n".$output."</div>\n";
        }

        return $output;
    }
}
