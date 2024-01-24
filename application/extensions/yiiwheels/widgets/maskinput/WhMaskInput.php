<?php
/**
 * WhMaskMoney widget class
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.maskInput
 * @uses YiiStrap.helpers.TbHtml
 */

Yii::import('yiistrap_fork.helpers.TbArray');

class WhMaskInput extends CInputWidget
{

    /**
     * @var array the plugin options
     * @see http://igorescobar.github.io/jQuery-Mask-Plugin/
     */
    public $pluginOptions;

    /**
     * @var string
     */
    public $mask = '';

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
     * Renders the input field
     */
    public function renderField()
    {
        list($name, $id) = $this->resolveNameID();

        TbArray::defaultValue('id', $id, $this->htmlOptions);
        TbArray::defaultValue('name', $name, $this->htmlOptions);

        if ($this->hasModel()) {
            echo CHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
        } else {
            echo CHtml::textField($this->name, $this->value, $this->htmlOptions);
        }
    }

    /**
     * Registers required client script for jquery mask plugin. It doesn't use bootstrap->registerPlugin.
     */
    public function registerClientScript()
    {
        /* publish assets dir */
        $path      = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        $assetsUrl = $this->getAssetsUrl($path);

        /* @var $cs CClientScript */
        $cs = Yii::app()->getClientScript();

        $cs->registerScriptFile($assetsUrl . '/js/jquery.mask.js');

        /* initialize plugin */
        $selector = '#' . TbArray::getValue('id', $this->htmlOptions, $this->getId());

        $options = !empty($this->pluginOptions) ? CJavaScript::encode($this->pluginOptions) : '{}';
        $script = "jQuery('{$selector}').mask('{$this->mask}',{$options});";
        Yii::app()->clientScript->registerScript((uniqid(__CLASS__ . '#', true)), $script, CClientScript::POS_END);
    }
}
