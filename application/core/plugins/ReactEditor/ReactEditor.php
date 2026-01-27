<?php

namespace ReactEditor;

class ReactEditor extends \PluginBase
{
    /**
     * @return void
     */
    public function init()
    {
        $this->subscribe('beforeControllerAction');
        $this->subscribe('beforeAdminMenuRender');
    }

    public function beforeControllerAction(): void
    {
        $this->renderActivateEditorModal();
    }

    /**
     * Append new menu item to the admin topbar
     */
    public function beforeAdminMenuRender(): void
    {
        //$adminMenuDropdownItems = new \LimeSurveyProfessional\adminMenuDropdownItems\AdminMenuDropdownItems();
        $this->renderDropdownItems();
    }

    /**
     * @return string
     */
    public function renderActivateEditorModal()
    {
        return $this->renderPartial(
            'views/_modalActivateDeactivateEditor', [
            'activated' => true
            ],
            false,
            true
        );
    }

    public function renderDropdownItems()
    {
        /*
        $assetsUrl = \Yii::app()->assetManager->publish(
            dirname(__FILE__) . '/../js'
        );
        App()->clientScript->registerScriptFile($assetsUrl . '/adminMenuDropdown.js'); */

        $assetsUrl = \Yii::app()->assetManager->publish(dirname(__FILE__) . '/../js');
        \Yii::app()->clientScript->registerScriptFile($assetsUrl . '/adminMenuDropdown.js');

        $htmlLiItems = json_encode(
            $this->renderPartial(
                'views/_activateEditorItem',
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
