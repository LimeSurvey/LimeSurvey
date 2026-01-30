<?php

class ReactEditor extends \PluginBase
{

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
        $controllerAction = ($controller === 'surveyAdministration') && ($action === 'view');

        $editorEnabled = Yii::app()->getConfig('editorEnabled');
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
        $modalHtml = $this->renderPartial(
            '_modalActivateDeactivateEditor', [
            'activated' => true
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



}
