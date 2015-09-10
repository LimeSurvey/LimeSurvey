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
Yii::import('yiiwheels.widgets.formhelpers.WhInputWidget');

class WhDatePickerHelper extends WhInputWidget
{

    public function init()
    {
        parent::init();
        TbHtml::addCssClass('bfh-datepicker', $this->htmlOptions);

    }

    public function run()
    {
        echo CHtml::tag('div', $this->htmlOptions, '');

        $this->registerPlugin('bfhdatepicker');
    }
}