<?php
/**
 * WhAceEditor widget class
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @author Matt Tabin <amigo.tabin@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.ace
 * @uses Yiistrap.helpers.TbHtml
 */
Yii::import('bootstrap.helpers.TbHtml');
Yii::import('bootstrap.helpers.TbArray');

class WhAceEditor extends CInputWidget
{
    /**
     * @var string the theme
     */
    public $theme = 'clouds';

    /**
     * @var string the editor mode
     */
    public $mode = 'html';

    /**
     * @var array the options for the ace editor
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
        if (empty($this->theme)) {
            throw new CException(Yii::t(
                'zii',
                '"{attribute}" cannot be empty.',
                array('{attribute}' => 'theme')
            ));
        }

        if (empty($this->mode)) {
            throw new CException(Yii::t(
                'zii',
                '"{attribute}" cannot be empty.',
                array('{attribute}' => 'mode')
            ));
        }

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
     * Renders field
     */
    public function renderField()
    {
        list($name, $id) = $this->resolveNameID();

        TbArray::defaultValue('id', $id, $this->htmlOptions);
        TbArray::defaultValue('name', $name, $this->htmlOptions);

        $tagOptions = $this->htmlOptions;

        $tagOptions['id'] = 'aceEditor_' . $tagOptions['id'];

        echo CHtml::openTag('div', $tagOptions);
        echo CHtml::closeTag('div');

        $this->htmlOptions['style'] = 'display:none';

        if ($this->hasModel()) {
            echo CHtml::activeTextArea($this->model, $this->attribute, $this->htmlOptions);
        } else {
            echo CHtml::textArea($name, $this->value, $this->htmlOptions);
        }

        $this->htmlOptions = $tagOptions;
        if (!isset($this->htmlOptions['textareaId']))
            $this->htmlOptions['textareaId'] = $id;
    }

    /**
     * Registers required client script for bootstrap ace editor.
     */
    public function registerClientScript()
    {
        /* publish assets dir */
        $path      = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        $assetsUrl = $this->getAssetsUrl($path);

        /* @var $cs CClientScript */
        $cs = Yii::app()->getClientScript();

        $cs->registerScriptFile($assetsUrl . '/js/ace.js', CClientScript::POS_END);

        $id = TbArray::getValue('id', $this->htmlOptions, $this->getId());

        /* Global value that will hold the editor */
        $cs->registerScript(uniqid(__CLASS__ . '#' . $id, true), 'var ' . $id . ';', CClientScript::POS_HEAD);

        ob_start();
        /* initialize plugin */
        $selector = TbArray::getValue('id', $this->htmlOptions, $this->getId());

        echo $selector . '= ace.edit("' . $id . '");' . PHP_EOL;
        echo $selector . '.setTheme("ace/theme/' . $this->theme . '");' . PHP_EOL;
        echo $selector . '.getSession().setMode('.(is_array($this->mode) ? CJavaScript::encode($this->mode) : '"ace/mode/'.$this->mode.'"').');' . PHP_EOL;
        echo $selector . '.setValue($("#'.$this->htmlOptions['textareaId'].'").val());' . PHP_EOL;
        echo $selector . '.getSession().on("change", function(){
                var theVal = ' . $selector . '.getSession().getValue();
                $("#'.$this->htmlOptions['textareaId'].'").val(theVal);
            });';
            
        if (!empty($this->events) && is_array($this->events)) {
            foreach ($this->events as $name => $handler) {
                $handler = ($handler instanceof CJavaScriptExpression)
                    ? $handler
                    : new CJavaScriptExpression($handler);

                echo $id . ".getSession().on('{$name}', {$handler});" . PHP_EOL;
            }
        }
        
        if (!empty($this->pluginOptions))
            echo $selector . '.setOptions('.CJavaScript::encode($this->pluginOptions).')';

        $cs->registerScript(uniqid(__CLASS__ . '#ReadyJS' . $id, true), ob_get_clean());
    }
}
