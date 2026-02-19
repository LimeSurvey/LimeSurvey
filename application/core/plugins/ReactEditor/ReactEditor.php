<?php

use LimeSurvey\Models\Services\EditorService;

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
        $this->subscribe('beforeAdminMenuRender');
        $this->subscribe('newDirectRequest');
        $this->subscribe('beforeDeactivate');
        $this->subscribe('beforeControllerAction', 'initEditor');
        $this->subscribe('beforeControllerAction', 'renderActivateEditorModal');
        $this->subscribe('beforeRenderSurveySidemenu');
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
        EditorService::initEditorApp();
    }

    /**
     * renders the survey side-menu to link the classic editor to the React editor
     */
    public function beforeRenderSurveySidemenu()
    {
        EditorService::beforeRenderSurveySidemenu($this);
    }

    /**
     * Append new menu item to the admin topbar
     */
    public function beforeAdminMenuRender(): void
    {
        $this->renderDropdownItems();
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
                    'activated' => $this->isEditorEnabled(),
                    'hasPathUrlFormat' => $this->hasPathUrlFormat()
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

    public function renderDropdownItems()
    {
        $assetsUrl = \Yii::app()->assetManager->publish(dirname(__FILE__) . '/js');
        \Yii::app()->clientScript->registerScriptFile($assetsUrl . '/adminMenuDropdown.js', LSYii_ClientScript::POS_HEAD);

        $htmlLiItems = json_encode(
            $this->renderPartial(
                '_activateEditorItem',
                [],
                true
            ),
            JSON_HEX_APOS
        );

        //li items accessible for js
        App()->clientScript->registerScript(
            'liItemsJsHtml',
            <<<EOT
                function getItemsHtml() {

                    return $htmlLiItems;
                }
EOT,
            \CClientScript::POS_BEGIN
        );
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
    private function createUniqueWrongUrlFormatNotification()
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
    private function isEditorEnabled($urlFormatCheck = true) {
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

    private function hasPathUrlFormat(): bool
    {
//        return false;
         return App()->getUrlManager()->getUrlFormat() === 'path';
    }
}
