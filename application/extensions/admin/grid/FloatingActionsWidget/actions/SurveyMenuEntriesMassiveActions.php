<?php

namespace actions;

/**
 * Action definitions for the Survey Menu Entries floating action bar.
 */
class SurveyMenuEntriesMassiveActions
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
                'url' => \App()->createUrl('/admin/menuentries/sa/batchEdit/'),
                'iconClasses' => 'ri-pencil-line',
                'text' => \gT('Batch edit'),
                'grid-reload' => 'yes',
                'actionType' => 'modal',
                'modalType' => 'cancel-save',
                'keepopen' => 'yes',
                'yes' => \gT('Save'),
                'no' => \gT('Cancel'),
                'sModalTitle' => \gT('Batch edit the menu entries'),
                'htmlModalBody' => \Yii::app()->getController()->renderPartial(
                    './surveymenu_entries/massive_action/_update',
                    [],
                    true
                ),
            ];
        }

        if (\Permission::model()->hasGlobalPermission('settings', 'delete')) {
            $buttons[] = [
                'type' => 'action',
                'action' => 'delete',
                'url' => \App()->createUrl('/admin/menuentries/sa/massDelete/'),
                'iconClasses' => 'ri-delete-bin-fill',
                'btnClass' => 'floating-delete-button',
                'text' => \gT('Delete'),
                'grid-reload' => 'yes',
                'actionType' => 'modal',
                'modalType' => 'cancel-delete',
                'keepopen' => 'yes',
                'sModalTitle' => \gT('Delete menu entries'),
                'htmlModalBody' => \gT('Are you sure you want to delete the selected menu entries?'),
                'aCustomDatas' => [],
            ];
        }

        return $buttons;
    }
}
