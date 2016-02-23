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

class WhTimezones extends WhDropDownInputWidget
{

    /**
     * @var string the two letter country code or ID of a bfh-countries HTML element. To filter based on a country.
     * It is required.
     */
    public $country;

    public function init()
    {
        if (empty($this->country) && !isset($this->pluginOptions['country'])) {
            throw new CException('"$country" cannot be empty.');
        }

        $this->pluginOptions['country'] = TbArray::getValue('country', $this->pluginOptions, $this->country);

        parent::init();

        TbHtml::addCssClass('bfh-timezones', $this->htmlOptions);

        unset($this->htmlOptions['data-name']);
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