<?php

use LimeSurvey\Models\Services\EditorService;

class ReactEditor extends PluginBase
{
    /**
     * @return void
     */
    public function init()
    {
        $this->subscribe('beforeControllerAction', 'initEditor');
        $this->subscribe('beforeControllerAction', 'registerSurveyRedirect');
    }

    public function initEditor()
    {
        EditorService::initEditorApp(
            SettingsUser::getUserSettingValue('editorEnabled')
        );
    }

    public function registerSurveyRedirect()
    {
        $controller = $this->getEvent()->get('controller');
        $action = $this->getEvent()->get('action');

        EditorService::registerSurveyRedirect(
            SettingsUser::getUserSettingValue('editorEnabled'),
            $controller,
            $action
        );
    }

}
