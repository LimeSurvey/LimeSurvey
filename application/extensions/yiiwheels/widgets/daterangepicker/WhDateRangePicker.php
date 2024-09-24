<?php
/**
 * WhDateRangePicker widget class
 * A simple implementation for date range picker for Twitter Bootstrap
 * @see <http://www.dangrossman.info/2012/08/20/a-date-range-picker-for-twitter-bootstrap/>
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.daterangepicker
 * @uses YiiStrap.helpers.TbArray
 */
Yii::import('yiistrap_fork.helpers.TbArray');

class WhDateRangePicker extends CInputWidget
{

    /**
     * @var string $selector if provided, then no input field will be rendered. It will write the JS code for the
     * specified selector.
     */
    public $selector;

    /**
     * @var string JS Callback for Daterange picker
     */
    public $callback;

    /**
     * @var array pluginOptions to be passed to daterange picker plugin
     */
    public $pluginOptions = array();

    /**
     * Initializes the widget.
     */
    public function init()
    {
        $this->attachBehavior('ywplugin', array('class' => 'yiiwheels.behaviors.WhPlugin'));
        $this->htmlOptions['id'] = TbArray::getValue('id', $this->htmlOptions, $this->getId());
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
     * Renders the field if no selector has been provided
     */
    public function renderField()
    {
        if (null === $this->selector) {
            list($name, $id) = $this->resolveNameID();

            if ($this->hasModel()) {
                echo TbHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
            } else {
                echo TbHtml::textField($name, $this->value, $this->htmlOptions);
            }

            $this->setLocaleSettings();
        }
    }

    /**
     *
     * If user did not provided the names of weekdays and months in $this->pluginOptions['locale']
     *  (which he should not care about anyway)
     *  then we populate this names from Yii's locales database.
     *
     * <strong>Heads up!</strong> This method works with the local properties directly.
     */
    private function setLocaleSettings()
    {
        $this->setDaysOfWeekNames();
        $this->setMonthNames();
    }

    /**
     * Sets days of week names if no locale settings were made to the plugin options.
     */
    private function setDaysOfWeekNames()
    {
        if (empty($this->pluginOptions['locale']['daysOfWeek'])) {
            $this->pluginOptions['locale']['daysOfWeek'] = Yii::app()->locale->getWeekDayNames('narrow', true);
        }
    }

    /**
     * Sets month names if no locale settings were made to the plugin options.
     */
    private function setMonthNames()
    {
        if (empty($this->pluginOptions['locale']['monthNames'])) {
            $this->pluginOptions['locale']['monthNames'] = array_values(
                Yii::app()->locale->getMonthNames('wide', true)
            );
        }
    }

    /**
     *
     * Registers required css js files
     */
    public function registerClientScript()
    {
        /* publish assets dir */
        $path      = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        $assetsUrl = $this->getAssetsUrl($path);

        /* register required moment.js */
        $this->getYiiWheels()->registerAssetJs('moment-with-locales.js');

        /* @var $cs CClientScript */
        $cs = Yii::app()->getClientScript();

        $cs->registerCssFile($assetsUrl . '/css/daterangepicker.css');
        $cs->registerScriptFile($assetsUrl . '/js/daterangepicker.js', CClientScript::POS_END);

        /* initialize plugin */
        $selector = $this->selector ?? '#' . TbArray::getValue('id', $this->htmlOptions, $this->getId());

        $callback = ($this->callback instanceof CJavaScriptExpression)
            ? $this->callback
            : ($this->callback === null ? '' : new CJavaScriptExpression($this->callback));

        $cs->registerScript(
            __CLASS__ . '#' . $this->getId(),
            '$("' . $selector . '").daterangepicker(' .
                CJavaScript::encode($this->pluginOptions) .
                ($callback ? ', ' . CJavaScript::encode($callback) : '') .
                ');'
        );
    }
}
