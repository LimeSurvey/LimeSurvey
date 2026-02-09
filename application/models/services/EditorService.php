<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\EditorService\EditorConfig;
use LimeSurvey\Models\Services\EditorService\EditorRedirector;
use LimeSurvey\Models\Services\EditorService\EditorRequestHelper;
use SettingsUser;

class EditorService
{
    public static function init()
    {
        $editorConfig = new EditorConfig(
            SettingsUser::getUserSettingValue('editorEnabled') ?? false
        );
        $editorConfig->initAppConfig();
    }

    public static function initEditorApp()
    {
        self::init();
        $editorRedirector = new EditorRedirector();
        $editorRedirector->handleRedirect();
    }


    public static function beforeRenderSurveySidemenu($event)
    {
        self::init();
        $surveyId = EditorRequestHelper::findSurveyId();
        if (App()->getConfig('editorEnabled') && !empty($surveyId)) {
            $event->getEvent()->set('sidemenu', true);
            App()->controller->widget(
                'ext.admin.survey.SurveySidemenuWidget.SurveySidemenuWidget',
                ['sid' => $surveyId]
            );
        }
    }
}
