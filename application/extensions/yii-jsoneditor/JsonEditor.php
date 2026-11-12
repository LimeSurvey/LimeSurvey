<?php

/**
 * Input widget rendering a JSON editor (jsoneditor library) for a JSON string value.
 * The JSON string is kept in a hidden textarea, which is the actual form field that gets submitted;
 * the editor writes every change back into that textarea.
 * Used for plugin settings of type 'json' (see SettingsWidget::renderJson()).
 *
 * @author Sam Mousa <sam@befound.nl>
 */
class JsonEditor extends CInputWidget
{
    /** @var string URL of the published jsoneditor library directory, with trailing slash */
    protected $baseUrl;

    /** @var array Options passed to the jsoneditor JavaScript constructor */
    public $editorOptions = array(
        'mode' => 'form',
        'modes' => array('form', 'code', 'tree', 'text')
    );

    /** @var array HTML attributes of the wrapper div around the textarea and the editor */
    public $htmlOptions = array(
        'class' => 'jsoneditor-wrapper'
    );

    /** @var string Name of the bundled jsoneditor library directory */
    protected $libraryDir = 'jsoneditor-2.3.6';

    /**
     * Publishes the jsoneditor library assets and registers the needed client scripts.
     * @return void
     */
    public function init()
    {
        $this->baseUrl = Yii::app()->assetManager->publish(__DIR__ . "/" . $this->libraryDir) . "/";
        $this->registerClientScript();
    }

    /**
     * Registers a CSS file from the published jsoneditor library.
     * @param string $fileName File path relative to the library directory
     * @return void
     */
    protected function registerCssFile($fileName)
    {
        App()->clientScript->registerCssFile($this->baseUrl . $fileName);
    }

    /**
     * Registers a script file from the published jsoneditor library.
     * @param string $fileName File path relative to the library directory
     * @return void
     */
    protected function registerScriptFile($fileName)
    {
        App()->clientScript->registerScriptFile($this->baseUrl . $fileName);
    }

    /**
     * Registers the jsoneditor library files and the jQuery plugin (widget.js) initialising it.
     * @return void
     */
    protected function registerClientScript()
    {
        $this->registerCssFile('jsoneditor-min.css');
        $this->registerScriptFile('jsoneditor-min.js');
        $this->registerScriptFile('lib/ace/ace.js');
        App()->clientScript->registerScriptFile(App()->assetManager->publish(__DIR__ . '/widget.js'));
    }


    /**
     * Renders the textarea holding the JSON string and registers the editor initialisation.
     * The textarea content is HTML-encoded so that the browser hands the exact JSON string to the editor.
     * @return void
     */
    public function run()
    {
        $htmlOptions = $this->htmlOptions;
        list($name, $id) = $this->resolveNameID();
        $value = $this->getJsonValue();

        echo CHtml::tag('div', $htmlOptions, CHtml::textArea($name, $value, array(
            'id' => $id,
        )));
        $config = json_encode($this->editorOptions);
        App()->getClientScript()->registerScript("initJsonEditor" . $id, "$('#{$id}').jsonEditor($config);", CClientScript::POS_READY);
    }

    /**
     * Returns the widget value as a JSON string.
     * A string that already is valid JSON (including e.g. "[]" or "0") is returned unchanged,
     * an empty value is returned as an empty string and anything else is JSON encoded.
     * @return string
     */
    protected function getJsonValue()
    {
        $value = $this->value;
        if ($value === null || $value === '') {
            return '';
        }
        if (is_string($value)) {
            json_decode($value);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $value;
            }
        }
        return json_encode($value);
    }
}
