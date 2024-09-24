<?php
/**
 * WhTypeAhead widget class
 *
 * @see https://github.com/twitter/typeahead.js
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.typeahead
 * @uses YiiStrap.helpers.TbArray
 */
Yii::import('yiistrap_fork.helpers.TbArray');

class WhTypeAhead extends CInputWidget
{

    /**
     * @var array the plugin options
     * @see https://github.com/twitter/typeahead.js
     */
    public $pluginOptions;

    /**
     * @var bool whether to display minified versions of the files or not
     */
    public $debugMode = false;

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
     * Renders the typeahead field
     */
    public function renderField()
    {
        list($name, $id) = $this->resolveNameID();

        TbArray::defaultValue('id', $id, $this->htmlOptions);
        TbArray::defaultValue('name', $name, $this->htmlOptions);

        TbHtml::addCssClass('form-control', $this->htmlOptions);

        if ($this->hasModel()) {
            echo CHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
        } else {
            echo CHtml::textField($this->name, $this->value, $this->htmlOptions);
        }
    }

    /**
     * Registers required client script for bootstrap typeahead. It is not used through bootstrap->registerPlugin
     * in order to attach events if any
     */
    public function registerClientScript()
    {
        /* publish assets dir */
        $path      = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        $assetsUrl = $this->getAssetsUrl($path);

        /* @var $cs CClientScript */
        $cs = Yii::app()->getClientScript();

        $min = $this->debugMode
            ? '.min'
            : '';

        $cs->registerCssFile($assetsUrl . '/css/typeahead' . $min . '.css');
        $cs->registerScriptFile($assetsUrl . '/js/typeahead' . $min . '.js', CClientScript::POS_END);

        /* initialize plugin */
        $selector = '#' . TbArray::getValue('id', $this->htmlOptions, $this->getId());

        $this->getApi()->registerPlugin('typeahead', $selector, $this->pluginOptions);
    }
}
