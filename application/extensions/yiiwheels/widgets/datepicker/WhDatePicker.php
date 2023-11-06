<?php
/**
 * WhDatePicker widget class
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.datepicker
 * @uses YiiStrap.helpers.TbHtml
 */
Yii::import('bootstrap.helpers.TbHtml');
Yii::import('bootstrap.helpers.TbArray');

class WhDatePicker extends CInputWidget
{
    /**
     * @var array the options for the Bootstrap JavaScript plugin.
     */
    public $pluginOptions = array();

    /**
     * @var string[] the JavaScript event handlers.
     */
    public $events = array();

    /**
     * Initializes the widget.
     */
    public function init()
    {
        $this->attachBehavior('ywplugin', array('class' => 'yiiwheels.behaviors.WhPlugin'));

        TbArray::defaultValue('autocomplete', 'off', $this->htmlOptions);
        TbHtml::addCssClass('grd-white', $this->htmlOptions);

        $this->initOptions();
    }

    /**
     * Initializes options
     */
    public function initOptions()
    {
        TbArray::defaultValue('format', 'mm/dd/yyyy', $this->pluginOptions);
        TbArray::defaultValue('autoclose', true, $this->pluginOptions);
    }

    /**
     * Runs the widget.
     */
    public function run()
    {
        $this->renderField();
        $this->registerClientScript();
    }

    /**
     * Renders field
     */
    public function renderField()
    {
        list($name, $id) = $this->resolveNameID();

        TbArray::defaultValue('id', $id, $this->htmlOptions);
        TbArray::defaultValue('name', $name, $this->htmlOptions);

        if ($this->hasModel()) {
            echo TbHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);

        } else {
            echo TbHtml::textField($name, $this->value, $this->htmlOptions);
        }
    }

    /**
     * Registers required client script for bootstrap datepicker.
     */
    public function registerClientScript()
    {
        /* publish assets dir */
        $path      = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        $assetsUrl = $this->getAssetsUrl($path);

        /* @var $cs CClientScript */
        $cs = Yii::app()->getClientScript();

        $cs->registerCssFile($assetsUrl . '/css/datepicker.css');
        $cs->registerScriptFile($assetsUrl . '/js/bootstrap-datepicker.js', CClientScript::POS_END);

        if ($language = TbArray::getValue('language', $this->pluginOptions)) {
            $cs->registerScriptFile(
                $assetsUrl . '/js/locales/bootstrap-datepicker.' . $language . '.js',
                CClientScript::POS_END
            );
        }

        /* initialize plugin */
        $selector = '#' . TbArray::getValue('id', $this->htmlOptions, $this->getId());

        $this->getApi()->registerPlugin('bdatepicker', $selector, $this->pluginOptions);
        $this->getApi()->registerEvents($selector, $this->events);

    }
}
