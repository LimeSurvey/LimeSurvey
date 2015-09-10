<?php
/**
 *
 * WhCountries.php
 *
 * Date: 06/09/14
 * Time: 14:17
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @link http://www.ramirezcobos.com/
 * @link http://www.2amigos.us/
 */
Yii::import('yiiwheels.widgets.formhelpers.WhDropDownInputWidget');

class WhGoogleFonts extends WhDropDownInputWidget
{

    public function init()
    {
        parent::init();
        TbHtml::addCssClass('bfh-googlefonts', $this->htmlOptions);

        if(!isset($this->htmlOptions['data-font'])) {
            $this->htmlOptions['data-font'] = TbArray::popValue('data-value', $this->htmlOptions);
        }
        unset($this->htmlOptions['data-name'], $this->htmlOptions['data-value']);
    }

    public function run()
    {
        if(!$this->readOnly) {
            echo $this->dropDownList();
        } else
        {
            echo CHtml::tag('span', $this->htmlOptions, '');
        }

        $this->registerPlugin('bfhgooglefonts');
    }
}