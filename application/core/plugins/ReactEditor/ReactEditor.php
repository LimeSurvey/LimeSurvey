<?php

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
        $this->subscribe('beforeControllerAction');
        $this->subscribe('beforeAdminMenuRender');
        $this->subscribe('newDirectRequest');
    }

    /**
     * @throws CException
     */
    public function beforeControllerAction(): void
    {
        $this->renderActivateEditorModal();

        //redirect to the new editor if turned on...
        $controller = $this->getEvent()->get('controller');
        $action = $this->getEvent()->get('action');

        //defining list of routes that should be redirected to the new editor
        $controllerAction = ($controller === 'surveyAdministration') && ($action === 'view');

        $editorEnabled = $this->isEditorEnabled();
        if ($controllerAction) {
            //todo check user permission
            $sid = sanitize_int(Yii::app()->request->getParam('surveyid'));
            if (Permission::model()->hasSurveyPermission((int)$sid, 'survey', 'read')) {
                $survey = Survey::model()->findByPk($sid);
                $fruityView = ($survey->getTemplateEffectiveName() === 'fruity_twentythree');
                if ($editorEnabled && $fruityView) {
                    Yii::app()->request->redirect(\EditorLinkController::REACT_APP_BASE_PATH . 'survey/' . $sid);
                }
            }
        }

    }

    /**
     * Append new menu item to the admin topbar
     */
    public function beforeAdminMenuRender(): void
    {
        $this->renderDropdownItems();
    }

    /**
     * @return string
     */
    public function renderActivateEditorModal()
    {

        $assetsUrl = \Yii::app()->assetManager->publish(dirname(__FILE__) . '/js');
        \Yii::app()->clientScript->registerScriptFile($assetsUrl . '/activateEditor.js');

        $modalHtml = $this->renderPartial(
            '_modalActivateDeactivateEditor', [
            'activated' => $this->isEditorEnabled(),
            ],
            true,
        );

        \Yii::app()->getClientScript()->registerScript(
            'previewModal',
            "
            // First, remove all existing modals
            $('div[id=\"feature-preview-modal\"]').remove();
            
            // Add the modal only once, with a flag to prevent duplication
            if (!window.featurePreviewModalAdded) {
                $('body').append(" . json_encode($modalHtml) . ");
                window.featurePreviewModalAdded = true;
            }
            "
        );
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
            //$activateDeactivate = new EditorEnableService($optIn);
            if($optIn === 1 || $optIn === 0) {
                $userId = App()->user->id;
                $userSetting = SettingsUser::model()->findByAttributes(
                    [
                        'uid' => $userId,
                        "stg_name" => self::STG_NAME_REACT_EDITOR
                    ]
                );
                if ($userSetting === null) {
                    //default value from config was used, create a new entry for the user
                    $userSetting = new SettingsUser();
                    $userSetting->uid = $userId;
                    $userSetting->stg_name = self::STG_NAME_REACT_EDITOR;
                    $userSetting->stg_value = $optIn;
                } else {
                    //here we can simply update the value
                    $userSetting->stg_value = $optIn;
                }
                $success = $userSetting->save();
            }

            //$activateDeactivate->activateDeactivateEditor();
        }


    }

    /**
     * Checks if react editor is enabled for the current user.
     *
     * @return bool|string
     */
    private function isEditorEnabled() {
        //first check db settings_user
        $userId = App()->user->id;
        $userSetting = SettingsUser::model()->findByAttributes(
            [
                'uid' => $userId,
                "stg_name" => self::STG_NAME_REACT_EDITOR
            ]
        );

        if ($userSetting) {
            return ($userSetting->stg_value === '1');
        }
        //if no entry there check the config default value
        return App()->getConfig(self::STG_NAME_REACT_EDITOR);
    }


}
