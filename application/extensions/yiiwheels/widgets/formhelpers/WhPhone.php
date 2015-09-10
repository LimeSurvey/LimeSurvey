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

class WhPhone extends WhInputWidget
{
    /**
     * @var string the formatting options
     */
    public $format = false;

    public function init()
    {
        parent::init();
        TbHtml::addCssClass('bfh-phone', $this->htmlOptions);

        $this->htmlOptions['data-format'] = $this->format;

        unset($this->htmlOptions['data-name'], $this->htmlOptions['data-value']);
    }

    public function run()
    {
        if (!$this->readOnly) {
            echo $this->hasModel()
                ? CHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions)
                : CHtml::textField($this->name, $this->value, $this->htmlOptions);
        } else {
            $this->htmlOptions['data-number'] = $this->hasModel()
                ? $this->model->{$this->attribute}
                : $this->value;
            echo CHtml::tag('span', $this->htmlOptions, '');
        }

        $this->registerPlugin('bfhphone');
    }
}