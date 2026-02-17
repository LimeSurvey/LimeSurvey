<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\EditorService\EditorConfig;
use LimeSurvey\Models\Services\EditorService\EditorRedirector;
use LimeSurvey\Models\Services\EditorService\EditorRequestHelper;
use SettingsUser;

class EditorService
{
    private static ?self $instance = null;
    public bool $editorStatus;
    public bool $editorAllowed;

    private function __construct(bool $editorStatus = true, bool $editorAllowed = true)
    {
        $this->editorStatus = $editorStatus;
        $this->editorAllowed = $editorAllowed;
    }

    public static function init(bool $editorStatus = true, bool $editorAllowed = true): self
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        return self::$instance = new self($editorStatus, $editorAllowed);
    }

    public static function initEditorApp()
    {
        $editorConfig = new EditorConfig(self::$instance->editorStatus ?? true);
        $editorConfig->initAppConfig();

        $editorRedirector = new EditorRedirector();
        $editorRedirector->handleRedirect();
    }

    public static function beforeRenderSurveySidemenu($event)
    {
        $editorConfig = new EditorConfig(self::$instance->editorStatus ?? true);
        $editorConfig->initAppConfig();

        $surveyId = EditorRequestHelper::findSurveyId();
        if (App()->getConfig('editorEnabled') && !empty($surveyId)) {
            $event->getEvent()->set('sidemenu', true);
            App()->controller->widget(
                'ext.admin.survey.SurveySidemenuWidget.SurveySidemenuWidget',
                ['sid' => $surveyId]
            );
        }
    }

    public static function beforeAdminMenuRender()
    {
        if (self::$instance && self::$instance->editorAllowed) {
            App()->clientScript->registerScript(
                'liReactItemJsHtmlHead',
                <<<EOT
                $(document).ready(function () {
                    $('#admin-menu-item-account').after(getReactEditorItem());
                });
            EOT,
                \CClientScript::POS_HEAD
            );

            $htmlLiItems = json_encode(
                App()->getController()->renderPartial(
                    "/admin/editor/_activateEditorItem",
                    [],
                    true
                ),
                JSON_HEX_APOS
            );

            //li items accessible for js
            App()->clientScript->registerScript(
                'liReactItemJsHtml',
                <<<EOT
                    function getReactEditorItem() {
                        return $htmlLiItems;
                    }
                EOT,
                \CClientScript::POS_BEGIN
            );
        }
    }
}
