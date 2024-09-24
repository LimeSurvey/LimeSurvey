<?php
 /**
 * 
 * WhInputWidget.php
 *
 * Date: 06/09/14
 * Time: 13:48
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @link http://www.ramirezcobos.com/
 * @link http://www.2amigos.us/
 */

Yii::import('yiistrap_fork.helpers.TbArray');
Yii::import('yiistrap_fork.helpers.TbHtml');

class WhInputWidget extends CInputWidget {
    /**
     * @var array the options for the Bootstrap FormHelper plugin.
     */
    public $pluginOptions = array();
    /**
     * @var array the event handlers for the underlying Bootstrap FormHelper input JS plugin.
     */
    public $clientEvents = array();
    /**
     * @var string the language code to use (default is english, no need to be set). Every plugin has its own available
     * languages.
     */
    public $language;
    /**
     * @var string in case you add your own language file.
     */
    public $languagePath;
    public $readOnly = false;
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!$this->hasModel() && $this->name === null) {
            throw new CException("Either 'name', or 'model' and 'attribute' properties must be specified.");
        }
        if (!isset($this->htmlOptions['id'])) {
            $this->htmlOptions['id'] = $this->hasModel()
                ? CHtml::activeId($this->model, $this->attribute)
                : $this->getId();
        }

        $this->attachBehavior('ywplugin', array('class' => 'yiiwheels.behaviors.WhPlugin'));

        $this->htmlOptions = TbArray::merge($this->asDataAttributes($this->pluginOptions), $this->htmlOptions);
        $this->pluginOptions = false;

        if ($this->hasModel()) {
            $this->htmlOptions['data-name'] = CHtml::activeId($this->model, $this->attribute);
            $this->htmlOptions['data-value'] = CHtml::value($this->model, $this->attribute);
        } else {
            $this->htmlOptions['data-name'] = $this->name;
            $this->htmlOptions['data-value'] = $this->value;
        }

    }

    /**
     * Converts client options to HTML5 data- attributes.
     * @param array $options the options to convert
     * @return array
     */
    protected function asDataAttributes($options)
    {
        $data = array();
        foreach ($options as $key => $value) {
            $data["data-$key"] = is_bool($value) ? ($value ? 'true' : 'false') : $value;
        }
        return $data;
    }

    /**
     * Registers a specific Bootstrap plugin and the related events
     * @param string $name the name of the Bootstrap helper plugin
     */
    protected function registerPlugin($name)
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'assets';
        $assetsUrl = $this->getAssetsUrl($path);

        /* @var $cs CClientScript */
        $cs = Yii::app()->getClientScript();
        $cs->registerCssFile($assetsUrl . "/css/bootstrap-formhelpers.min.css");
        $cs->registerScriptFile($assetsUrl . "/js/bootstrap-formhelpers.min.js", CClientScript::POS_END);

        if($this->language) {
            $fname = "bootstrap-formhelpers-" . (substr($name, 3)) . ".{$this->language}.js";
            $languageFile = $this->languagePath ? : $assetsUrl . "/i18n/{$this->language}/{$fname}";
            $cs->registerScriptFile($languageFile);
        }

        $id = $this->htmlOptions['id'];
        $js = array();
        if ($this->pluginOptions !== false) {
            $options = empty($this->pluginOptions) ? '' : CJavaScript::encode($this->pluginOptions);
            $js[] = "jQuery('#$id').{$name}({$options});";
        }

        if (!empty($this->clientEvents)) {
            foreach ($this->clientEvents as $event => $handler) {
                $js[] = "jQuery('#$id').on('$event', $handler);";
            }
        }
        if(count($js))
        {
            $cs->registerScript($id, implode("\n", $js));
        }
    }

} 
