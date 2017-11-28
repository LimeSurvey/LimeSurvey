<?php
/**
 * WhRedactor class
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.redactor
 * @uses YiiStrap.helpers.TbArray
 */
Yii::import('bootstrap.helpers.TbArray');

class WhRedactor extends CInputWidget
{
    /**
     * Editor options that will be passed to the editor
     * @see http://imperavi.com/redactor/docs/
     */
    public $pluginOptions = array();

    /**
     * Debug mode
     * Used to publish full js file instead of min version
     */
    public $debugMode = false;

    /**
     * Widget's init function
     */
    public function init()
    {

        $this->attachBehavior('ywplugin', array('class' => 'yiiwheels.behaviors.WhPlugin'));

        if (!$style = TbArray::popValue('style', $this->htmlOptions, '')) {
            $this->htmlOptions['style'] = $style;
        }

        $width                      = TbArray::getValue('width', $this->htmlOptions, '100%');
        $height                     = TbArray::popValue('height', $this->htmlOptions, '450px');
        $this->htmlOptions['style'] = "width:{$width};height:{$height};" . $this->htmlOptions['style'];
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
            echo CHtml::activeTextArea($this->model, $this->attribute, $this->htmlOptions);
        } else {
            echo CHtml::textArea($name, $this->value, $this->htmlOptions);
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

        $script = $this->debugMode
            ? 'redactor.js'
            : 'redactor.min.js';

        $cs->registerCssFile($assetsUrl . '/css/redactor.css');
        $cs->registerScriptFile($assetsUrl . '/js/' . $script, CClientScript::POS_BEGIN);

        /* register language */
        $language = TbArray::getValue('lang', $this->pluginOptions);
        if (!empty($language) && $language != 'en') {
            $cs->registerScriptFile($assetsUrl . '/js/langs/' . $language . '.js', CClientScript::POS_BEGIN);
        }

        /* register plugins (if any) */
        $this->registerPlugins($assetsUrl);

        /* initialize plugin */
        $selector = '#' . TbArray::getValue('id', $this->htmlOptions, $this->getId());

        $this->getApi()->registerPlugin('redactor', $selector, $this->pluginOptions);
    }

    /**
     * @param $assetsUrl
     */
    protected function registerPlugins($assetsUrl)
    {
        if (isset($this->pluginOptions['plugins'])) {
            $ds          = DIRECTORY_SEPARATOR;
            $pluginsPath = __DIR__ . $ds . 'assets' . $ds . 'js' . $ds . 'plugins' . $ds;
            $pluginsUrl  = $assetsUrl . '/js/plugins/';
            $scriptTypes = array('css', 'js');

            foreach ($this->pluginOptions['plugins'] as $pluginName) {
                foreach ($scriptTypes as $type) {
                    if (@file_exists($pluginsPath . $pluginName . $ds . $pluginName . '.' . $type)) {
                        Yii::app()->clientScript->registerScriptFile(
                            $pluginsUrl . '/' .
                                $pluginName . '/' .
                                $pluginName . '.' .
                                $type
                        );
                    }
                }
            }
        }
    }
}
