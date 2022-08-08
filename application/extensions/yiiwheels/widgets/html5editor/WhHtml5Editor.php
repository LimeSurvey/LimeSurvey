<?php
/**
 * WhHtml5Editor widget
 *
 * Implements the bootstrap-wysihtml5 editor
 *
 * @see https://github.com/jhollingworth/bootstrap-wysihtml5
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.highcharts
 * @uses YiiStrap.helpers.TbArray
 */
Yii::import('yiistrap_fork.helpers.TbArray');

class WhHtml5Editor extends CInputWidget
{
    /**
     * Editor language
     * Supports: de-DE, es-ES, fr-FR, pt-BR, sv-SE
     */
    public $lang = 'en';

    /**
     * Html options that will be assigned to the text area
     */
    public $htmlOptions = array();

    /**
     * Editor options that will be passed to the editor
     */
    public $pluginOptions = array();

    /**
     * Editor width
     */
    public $width = '100%';

    /**
     * Editor height
     */
    public $height = '400px';

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
     * Display editor
     */
    public function run()
    {

        list($name, $id) = $this->resolveNameID();

        $this->htmlOptions['id'] = $id;

        $this->registerClientScript();

        if (!array_key_exists('style', $this->htmlOptions))
            $this->htmlOptions['style'] = "width:{$this->width};height:{$this->height};";
        // Do we have a model?
        if ($this->hasModel())
            echo CHtml::activeTextArea($this->model, $this->attribute, $this->htmlOptions);
        else
            echo CHtml::textArea($name, $this->value, $this->htmlOptions);
    }

    /**
     * Register required script files
     */
    public function registerClientScript()
    {
        /* publish assets dir */
        $path      = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        $assetsUrl = $this->getAssetsUrl($path);

        /* @var $cs CClientScript */
        $cs = Yii::app()->getClientScript();

        $cs->registerCssFile($assetsUrl . '/css/bootstrap-wysihtml5.css');
        if (isset($this->pluginOptions['color'])) {
            $cs->registerCssFile($assetsUrl . '/css/wysiwyg-color.css');
        }

        $cs->registerScriptFile($assetsUrl . '/js/wysihtml5-0.3.0.js');
        $cs->registerScriptFile($assetsUrl . '/js/bootstrap-wysihtml5.js');

        if (in_array(@$this->pluginOptions['locale'], array('de-DE', 'es-ES', 'fr', 'fr-NL', 'pt-BR', 'sv-SE'))) {
            $cs->registerScriptFile(
                $assetsUrl . '/js/locale/bootstrap-wysihtml5.' . $this->pluginOptions['locale'] . '.js'
            );
        } elseif (in_array($this->lang, array('de-DE', 'es-ES', 'fr', 'fr-NL', 'pt-BR', 'sv-SE'))) {
            $cs->registerScriptFile($assetsUrl . '/js/locale/bootstrap-wysihtml5.' . $this->lang . '.js');
            $this->pluginOptions['locale'] = $this->lang;
        }

        $this->normalizeStylesheetsProperty();

        /* initialize plugin */
        $selector = '#' . TbArray::getValue('id', $this->htmlOptions, $this->getId());

        $this->getApi()->registerPlugin('wysihtml5', $selector, $this->pluginOptions);

    }

    /**
     * Normalizes stylesheet property
     */
    private function normalizeStylesheetsProperty()
    {
        if (empty($this->pluginOptions['stylesheets']))
            $this->pluginOptions['stylesheets'] = array();
        else if (is_array($this->pluginOptions['stylesheets']))
            $this->pluginOptions['stylesheets'] = array_filter($this->pluginOptions['stylesheets'], 'is_string');
        else if (is_string($this->pluginOptions['stylesheets']))
            $this->pluginOptions['stylesheets'] = array($this->pluginOptions['stylesheets']);
        else
            $this->pluginOptions['stylesheets'] = array();
    }
}
