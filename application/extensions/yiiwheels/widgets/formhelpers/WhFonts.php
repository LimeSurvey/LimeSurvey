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

class WhFonts extends WhDropDownInputWidget
{

    public function init()
    {
        parent::init();
        TbHtml::addCssClass('bfh-fonts', $this->htmlOptions);

        if (!isset($this->htmlOptions['data-font'])) {
            $this->htmlOptions['data-font'] = TbArray::popValue('data-value', $this->htmlOptions);
        }
        unset($this->htmlOptions['data-name'], $this->htmlOptions['data-value']);
    }

    public function run()
    {

        echo $this->dropDownList();

        $this->registerPlugin('bfhfonts');
    }
}