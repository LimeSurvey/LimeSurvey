<?php

class LanguagesWidget extends WhSelect2
{
    /**
     * Runs the widget.
     */
    public function run()
    {
        $this->registerSorter();
        parent::run();
    }

    private function registerSorter()
    {
        $this->pluginOptions['selectionAdapter'] = new CJavaScriptExpression('$.fn.select2.amd.require("select2/selection/languagesWidgetSelectionAdapter")');
        $this->pluginOptions['dataAdapter'] = new CJavaScriptExpression('$.fn.select2.amd.require("select2/data/languagesWidgetDataAdapter")');
        $this->pluginOptions['messages'] = [
            'cannotRemoveBaseLanguage' => gT("You cannot delete the base language. Please select a different language as base language, first."),
            'removeLanguageConfirmation' => gT("Are you sure, you want to delete this language? This will remove all survey content of this language permanently."),
            'delete' => gT("Delete"),
            'cancel' => gT("Cancel"),
        ];
    }

    public function registerClientScript()
    {
        $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        $scriptUrl = Yii::app()->getAssetManager()->publish($path . '/js/LanguagesWidget.js');
        $cssUrl = Yii::app()->getAssetManager()->publish($path . '/css/LanguagesWidget.css');
        Yii::app()->getClientScript()->registerScriptFile($scriptUrl, LSYii_ClientScript::POS_BEGIN);
        Yii::app()->getClientScript()->registerCssFile($cssUrl);

        parent::registerClientScript();
    }
}