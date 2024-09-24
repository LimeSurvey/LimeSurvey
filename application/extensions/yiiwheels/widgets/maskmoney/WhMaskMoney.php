<?php
/**
 * WhMaskMoney widget class
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.maskmoney
 * @uses YiiStrap.helpers.TbArray
 */

Yii::import('yiistrap_fork.helpers.TbArray');

class WhMaskMoney extends CInputWidget
{

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
     * Renders the the input field
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
     * Registers required client script for maskmoney plugin.
     */
    public function registerClientScript()
    {
        /* publish assets dir */
        $path      = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        $assetsUrl = $this->getAssetsUrl($path);

        /* @var $cs CClientScript */
        $cs = Yii::app()->getClientScript();

        $cs->registerScriptFile($assetsUrl . '/js/jquery.maskmoney.js');

        /* initialize plugin */
        $selector = '#' . TbArray::getValue('id', $this->htmlOptions, $this->getId());

        $this->getApi()->registerPlugin('maskMoney', $selector, $this->pluginOptions);
    }
}
