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

class WhFontSizes extends WhDropDownInputWidget
{

    public function init()
    {
        parent::init();
        TbHtml::addCssClass('bfh-fontsizes', $this->htmlOptions);

        if (!isset($this->htmlOptions['data-fontsize'])) {
            $this->htmlOptions['data-fontsize'] = TbArray::popValue('data-value', $this->htmlOptions);
        }
        unset($this->htmlOptions['data-name'], $this->htmlOptions['data-value']);
    }

    public function run()
    {

        echo $this->dropDownList();

        $this->registerPlugin('bfhfontsize');
    }
}