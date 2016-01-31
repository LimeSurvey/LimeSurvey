<?php
/**
 * WhTimePicker widget class
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.timepicker
 * @uses YiiStrap.helpers.TbArray
 */
Yii::import('bootstrap.helpers.TbArray');

class WhTimePicker extends CInputWidget
{
    /**
     * @var array the options for the Bootstrap JavaScript plugin.
     * Available options:
     * template    string
     *      'dropdown' (default), Show picker in a dropdown
     *      'modal', Show picker in a modal
     *      false, Don't show a widget
     * minuteStep    integer    15    Specify a step for the minute field.
     * showSeconds    boolean    false    Show the seconds field.
     * secondStep    integer    15    Specify a step for the second field.
     * defaultTime    string
     *      'current' (default) Set to the current time.
     *      'value' Set to inputs current value
     *      false    Do not set a default time
     * showMeridian    boolean
     *      true (default)  12hr mode
     *      false24hr mode
     * showInputs    boolean
     *      true (default )Shows the text inputs in the widget.
     *      false Hide the text inputs in the widget
     * disableFocus    boolean    false    Disables the input from focusing. This is useful for touch screen devices that
     *          display a keyboard on input focus.
     * modalBackdrop    boolean    false    Show modal backdrop.
     */
    public $pluginOptions = array();

    /**
     * @var string[] the JavaScript event handlers.
     */
    public $events = array();

    /**
     * @var array the HTML attributes for the widget container.
     */
    public $htmlOptions = array();

    /**
     * Initializes the widget.
     */
    public function init()
    {
        $this->attachBehavior('ywplugin', array('class' => 'yiiwheels.behaviors.WhPlugin'));

        TbHtml::addCssClass('form-control', $this->htmlOptions);
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
     * Renders the field
     */
    public function renderField()
    {
        list($name, $id) = $this->resolveNameID();

        TbArray::defaultValue('id', $id, $this->htmlOptions);
        TbArray::defaultValue('name', $name, $this->htmlOptions);

        echo '<div class="input-group">';
        echo '<div class="input-group-addon"><span class="glyphicon glyphicon-time"></span></div>';
        if ($this->hasModel()) {
            echo CHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
        } else {
            echo CHtml::textField($name, $this->value, $this->htmlOptions, array('style' => 'width:100%'));
        }
        echo '</div>';
    }

    /**
     * Registers required javascript files
     * @param $id
     */
    public function registerClientScript()
    {
        /* publish assets dir */
        $path      = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        $assetsUrl = $this->getAssetsUrl($path);

        /* @var $cs CClientScript */
        $cs = Yii::app()->getClientScript();

        $cs->registerCssFile($assetsUrl . '/css/bootstrap-timepicker.min.css');
        $cs->registerScriptFile($assetsUrl . '/js/bootstrap-timepicker.min.js');

        /* initialize plugin */
        $selector = '#' . TbArray::getValue('id', $this->htmlOptions, $this->getId());

        $this->getApi()->registerPlugin('timepicker', $selector, $this->pluginOptions);
        $this->getApi()->registerEvents($selector, $this->events);
    }
}