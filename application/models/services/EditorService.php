<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\EditorService\EditorConfig;
use LimeSurvey\Models\Services\EditorService\EditorRedirector;
use SettingsUser;

class EditorService
{
    public static function initEditorApp($editorEnabled)
    {
        $editorConfig = new EditorConfig($editorEnabled);
        $editorConfig->initAppConfig();

        $editorRedirector = new EditorRedirector();
        $editorRedirector->handleRedirect();
    }

    public static function registerSurveyRedirect($editorEnabled, $controller, $action)
    {
        if (
            $editorEnabled
            && $controller == 'surveyAdministration'
            && $action == 'newSurvey'
        ) {
            App()->clientScript->registerScriptFile(
                dirname(__DIR__) .
                '/services/EditorService/js/redirectToQEAfterSurvey.js',
                \LSYii_ClientScript::POS_END
            );
        }
    }
}
