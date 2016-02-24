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

class WhCountries extends WhDropDownInputWidget
{

    public function init()
    {
        parent::init();
        TbHtml::addCssClass('bfh-countries', $this->htmlOptions);

        if(!isset($this->htmlOptions['data-country'])) {
            $this->htmlOptions['data-country'] = TbArray::popValue('data-value', $this->htmlOptions);
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

        $this->registerPlugin('bfhcountries');
    }
}