<?php

use LimeSurvey\Models\Services\EditorService;
use ReactEditor\EditorMessages;

// phpcs:disable
require_once(__DIR__ . '/autoload.php');
// phpcs:enable

class ReactEditor extends \PluginBase
{
    const STG_NAME_REACT_EDITOR = "editorEnabled";

    protected $storage = 'DbStorage';

    protected static $description = 'Activate/deactivate the new react editor';

    protected static $name = 'ReactEditor';

    /**
     * @return void
     */
    public function init()
    {
        $this->subscribe('afterSuccessfulLogin', 'createUniqueWrongUrlFormatNotification');
        $this->subscribe('newDirectRequest');
        $this->subscribe('beforeDeactivate');
        $this->subscribe('beforeControllerAction', 'initEditor');
        $this->subscribe('beforeControllerAction', 'renderActivateEditorModal');
        $this->subscribe('beforeRenderSurveySidemenu');
        $this->subscribe('beforeAdminMenuRender');
    }

    public function beforeDeactivate()
    {
        $this->getEvent()->set('success', false);
        $this->getEvent()->set('message', gT('Core plugin can not be disabled.'));
    }

    /**
     * redirects to the React editor if it is enabled
     */
    public function initEditor()
    {
        if ($this->isBackendAccess()) {
            $status = $this->isEditorEnabled();
            EditorService::init($status, true)->initEditorApp();
        }
    }

    /**
     * renders the survey side-menu to link the classic editor to the React editor
     */
    public function beforeRenderSurveySidemenu()
    {
        if ($this->isBackendAccess()) {
            $status = $this->isEditorEnabled();
            EditorService::init($status, true)->beforeRenderSurveySidemenu($this);
        }
    }

    /**
     * Append new menu item to the admin topbar
     */
    public function beforeAdminMenuRender(): void
    {
        if ($this->isBackendAccess()) {
            $status = $this->isEditorEnabled();
            EditorService::init($status, true)->beforeAdminMenuRender();
        }
    }

    /**
     * @return void
     */
    public function renderActivateEditorModal()
    {
        if ($this->isBackendAccess()) {
            $assetsUrl = \Yii::app()->assetManager->publish(
                dirname(__FILE__) . '/js'
            );
            \Yii::app()->clientScript->registerScriptFile(
                $assetsUrl . '/activateEditor.js'
            );

            $modalHtml = $this->renderPartial(
                '_modalActivateDeactivateEditor',
                [
                    'activated' => $this->isEditorEnabled(false),
                    'hasPathUrlFormat' => $this->hasPathUrlFormat(),
                    'warningHeader' => EditorMessages::getUrlFormatRequirementHeader(),
                    'warningMessage' => EditorMessages::getUrlFormatRequirementMessage(),
                ],
                true,
            );

            $shouldShowModal = !$this->hasEditorSettingInDatabase();

            \Yii::app()->getClientScript()->registerScript(
                'previewModal',
                "
            // First, remove all existing modals
            $('div[id=\"feature-preview-modal\"]').remove();
            
            // Add the modal only once, with a flag to prevent duplication
            if (!window.featurePreviewModalAdded) {
                $('body').append(" . json_encode($modalHtml) . ");
                window.featurePreviewModalAdded = true;
                
                "
                . ($shouldShowModal ? "$('#activate_editor').modal('show');" : "")
                . "
            }
            "
            );
        }
    }

    /**
     * Checks if the editorEnabled setting exists in settings_user table for current user
     *
     * @return bool
     */
    private function hasEditorSettingInDatabase()
    {
        return SettingsUser::getUserSetting(self::STG_NAME_REACT_EDITOR) !== null;
    }

    /**
     * Saves if the users wants to activate or deactivate the new react editor
     *
     * @return void
     * @throws CHttpException
     */
    public function newDirectRequest()
    {
        $event = $this->getEvent();
        if ($event->get('target') != 'ReactEditor') {
            return;
        }

        $action = $event->get('function');

        if ($action === 'saveActivateDeactivate') {
            $optIn = isset($_POST['optin']) ? (int)$_POST['optin'] : -1;
            //update or insert entry in settings_user
            if ($optIn === 1 || $optIn === 0) {
                SettingsUser::setUserSetting(self::STG_NAME_REACT_EDITOR, $optIn);
            }
        }
    }
    /**
     * Creates a unique notification for the current user when the React editor is enabled
     * but the URL format is not set to 'path'.
     *
     * This method checks if the editor is enabled and if the URL format requirement is not met.
     * If both conditions are true, it creates a high-importance notification informing the user
     * about the URL format requirement for the React editor.
     *
     * @return void
     */
    public function createUniqueWrongUrlFormatNotification()
    {
        if ($this->isEditorEnabled(false) && !$this->hasPathUrlFormat()) {
            $not = new UniqueNotification([
                'user_id' => App()->user->id,
                'importance' => Notification::HIGH_IMPORTANCE,
                'title' => EditorMessages::getUrlFormatRequirementHeader(),
                'message' => '<span class="ri-error-warning-fill"></span>&nbsp;'
                    .
                    EditorMessages::getUrlFormatRequirementMessage()
            ]);
            $not->save();
        }
    }

    /**
     * Checks if the React editor is enabled for the current user.
     *
     * This method first checks the user's settings in the database. If a user setting exists
     * and the URL format requirement is met (when checked), it returns the user's preference.
     * If no user setting exists, it falls back to the application's default configuration value,
     * still respecting the URL format requirement.
     *
     * @param bool $urlFormatCheck Whether to check if the URL format is set to 'path'.
     *                             Defaults to true. When false, skips the URL format validation.
     *
     * @return bool True if the React editor is enabled and URL format requirements are met
     *              (when checked), false otherwise.
     */
    private function isEditorEnabled(bool $urlFormatCheck = true): bool {
        //first check db settings_user
        $userSetting = SettingsUser::getUserSetting(self::STG_NAME_REACT_EDITOR);
        $hasPathUrlFormat = $urlFormatCheck ? $this->hasPathUrlFormat() : true;

        if ($userSetting && $hasPathUrlFormat) {
            return ($userSetting->stg_value === '1');
        }
        //if no entry there check the config default value
        return $hasPathUrlFormat && App()->getConfig(self::STG_NAME_REACT_EDITOR);
    }

    /**
     * If user is a logged-in user we can assume, that backend is accessed right now.
     */
    private function isBackendAccess(): bool
    {
        return !App()->user->isGuest;
    }

    /**
     * Checks if the application's URL format is set to 'path'.
     *
     * This method verifies whether the URL manager is configured to use the 'path' format,
     * which is required for the React editor to function properly.
     *
     * @return bool True if the URL format is 'path', false otherwise.
     */
    private function hasPathUrlFormat(): bool
    {
         return App()->getUrlManager()->getUrlFormat() === 'path';
    }
}
