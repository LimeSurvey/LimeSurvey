<?php
/**
 * Massive actions for the label sets grid.
 */

$this->widget(
    'ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget',
    [
        'pk' => 'lid',
        'gridid' => 'labelsets-grid',
        'dropupId' => 'labelSetsListActions',
        'dropUpText' => gT('Selected label set(s)...'),
        'aActions' => [
            [
                'type' => 'action',
                'action' => 'delete',
                'url' => App()->createUrl('/admin/labels/sa/massDelete'),
                'iconClasses' => 'ri-delete-bin-fill text-danger',
                'text' => gT('Delete'),
                'grid-reload' => 'yes',
                'on-success' => '(function(result) { LS.AjaxHelper.onSuccess(result); })',
                'actionType' => 'modal',
                'modalType' => 'cancel-delete',
                'keepopen' => 'no',
                'sModalTitle' => gT('Delete label sets'),
                'htmlModalBody' => gT('Are you sure you want to delete the selected label sets?'),
            ],
        ],
    ]
);
