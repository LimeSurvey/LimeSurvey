<?php

use LimeSurvey\Models\Services\EditorService;

class ReactEditor extends PluginBase
{
    /**
     * @return void
     */
    public function init()
    {
        $this->subscribe('beforeDeactivate');
        $this->subscribe('beforeControllerAction', 'initEditor');
        $this->subscribe('beforeControllerAction', 'registerSurveyRedirect');
    }

    public function beforeDeactivate()
    {
        $this->getEvent()->set('success', false);
        $this->getEvent()->set('message', gT('Core plugin can not be disabled.'));
    }

    public function initEditor()
    {
        EditorService::initEditorApp(
            SettingsUser::getUserSettingValue('editorEnabled') ?? false
        );
    }

    public function registerSurveyRedirect()
    {
        $controller = $this->getEvent()->get('controller');
        $action = $this->getEvent()->get('action');

        EditorService::registerSurveyRedirect(
            SettingsUser::getUserSettingValue('editorEnabled') ?? false,
            $controller,
            $action
        );
    }

}
