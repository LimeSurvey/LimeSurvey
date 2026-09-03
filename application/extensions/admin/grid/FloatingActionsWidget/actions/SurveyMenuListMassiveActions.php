<?php

namespace actions;

/**
 * Action definitions for the Survey Menus floating action bar.
 */
class SurveyMenuListMassiveActions
{
    /**
     * @return array
     */
    public static function getActions(): array
    {
        $buttons = [];

        if (\Permission::model()->hasGlobalPermission('settings', 'update')) {
            $buttons[] = [
                'type' => 'action',
                'action' => 'batchEdit',
                'url' => \App()->createUrl('/admin/menus/sa/batchEdit/'),
                'iconClasses' => 'ri-download-fill',
                'text' => \gT('Batch edit'),
                'grid-reload' => 'yes',
                'actionType' => 'modal',
                'modalType' => 'cancel-save',
                'keepopen' => 'yes',
                'yes' => \gT('Save'),
                'no' => \gT('Cancel'),
                'sModalTitle' => \gT('Batch edit the menus'),
                'htmlModalBody' => \Yii::app()->getController()->renderPartial(
                    './surveymenu/massive_action/_update',
                    [],
                    true
                ),
            ];
        }

        if (\Permission::model()->hasGlobalPermission('settings', 'delete')) {
            $buttons[] = [
                'type' => 'action',
                'action' => 'delete',
                'url' => \App()->createUrl('/admin/menus/sa/massDelete/'),
                'iconClasses' => 'ri-delete-bin-fill',
                'btnClass' => 'text-danger',
                'text' => \gT('Delete'),
                'grid-reload' => 'yes',
                'actionType' => 'modal',
                'modalType' => 'cancel-delete',
                'keepopen' => 'no',
                'sModalTitle' => \gT('Delete menus'),
                'htmlModalBody' => \gT('Are you sure you want to delete the selected menus and all related submenus and entries?'),
                'aCustomDatas' => [],
            ];
        }

        return $buttons;
    }
}

