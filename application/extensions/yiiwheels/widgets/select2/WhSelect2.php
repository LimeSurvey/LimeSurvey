<?php
/**
 * WhSelect2 widget class
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.select2
 * @uses YiiStrap.helpers.TbArray
 */
Yii::import('yiistrap_fork.helpers.TbArray');

class WhSelect2 extends CInputWidget
{

    /**
     * @var array @param data for generating the list options (value=>display)
     */
    public $data = array();

    /**
     * @var string[] the JavaScript event handlers.
     */
    public $events = array();

    /**
     * @var bool whether to display a dropdown select box or use it for tagging
     */
    public $asDropDownList = true;

    /**
     * @var string locale. Defaults to null. Possible values: "it"
     */
    public $language;

    /**
     * @var array the plugin options
     */
    public $pluginOptions;

    /**
     * Initializes the widget.
     */
    public function init()
    {

        $this->attachBehavior('ywplugin', array('class' => 'yiiwheels.behaviors.WhPlugin'));
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
     * Renders the select2 field
     */
    public function renderField()
    {
        list($name, $id) = $this->resolveNameID();

        TbArray::defaultValue('id', $id, $this->htmlOptions);
        TbArray::defaultValue('name', $name, $this->htmlOptions);

        if ($this->hasModel()) {
            echo $this->asDropDownList ?
                TbHtml::activeDropDownList($this->model, $this->attribute, $this->data, $this->htmlOptions) :
                TbHtml::activeHiddenField($this->model, $this->attribute);

        } else {
            echo $this->asDropDownList ?
                TbHtml::dropDownList($this->name, $this->value, $this->data, $this->htmlOptions) :
                TbHtml::hiddenField($this->name, $this->value);
        }
    }

    /**
     * Registers required client script for bootstrap select2. It is not used through bootstrap->registerPlugin
     * in order to attach events if any
     */
    public function registerClientScript()
    {
        /* publish assets dir */
        $path      = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        $assetsUrl = $this->getAssetsUrl($path);

        /* @var $cs CClientScript */
        $cs = Yii::app()->getClientScript();

        $cs->registerPackage('select2-bootstrap');


        if ($this->language) {
            $cs->registerScriptFile(
                $assetsUrl . '/js/i18n/' . $this->language . '.js',
                CClientScript::POS_POSTSCRIPT
            );
        }

        /* initialize plugin */
        $selector = '#' . TbArray::getValue('id', $this->htmlOptions, $this->getId());
        $this->pluginOptions['theme']='bootstrap-5';
        $this->getApi()->registerPlugin('select2', $selector, $this->pluginOptions, LSYii_ClientScript::POS_POSTSCRIPT);
        $this->getApi()->registerEvents($selector, $this->events, LSYii_ClientScript::POS_POSTSCRIPT);
    }
}
