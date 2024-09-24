<?php
/**
 * Render the selector for surveys massive actions.
 *
 */
?>


<!-- Rendering massive action widget -->
<?php
    $buttons = array();
    if (Permission::model()->hasGlobalPermission('settings','read')) {
        // Delete
        $buttons[] = array(
            // li element
            'type'        => 'action',
            'action'      => 'delete',
            'url'         =>  App()->createUrl('/admin/menuentries/sa/massDelete'),
            'iconClasses' => 'ri-delete-bin-fill text-danger',
            'text'        =>  gT('Delete'),
            'grid-reload' => 'yes',

            // modal
            'actionType'    => 'modal',
            'modalType'     => 'cancel-delete',
            'keepopen'      => 'no',
            'sModalTitle'   => gT('Delete menu entries'),
            'htmlModalBody' => gT('Are you sure you want to delete the selected menu entries?')
        );
    }

    if (Permission::model()->hasGlobalPermission('settings','read')) {
        // Download ZIP archive of file upload question types
        $buttons[] = array(
            'type' => 'action',
            'action' => 'batchEdit',
            'url' => App()->createUrl('/admin/menuentries/sa/batchEdit'),
            'iconClasses' => 'ri-file-edit-line',
            'text' => gT('Batch edit'),
            'grid-reload' => 'yes',
            //modal
            'actionType' => 'modal',
            'modalType'     => 'cancel-save',
            'keepopen'      => 'yes',
            'yes'           => gT('Save'),
            'no'            => gT('Cancel'),
            'sModalTitle'   => gT('Batch edit the menus'),
            'htmlModalBody' => $this->renderPartial('./surveymenu_entries/massive_action/_update', [], true),
        );

    }

    $this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', array(
            'pk'          => 'id',
            'gridid'      => 'surveymenu-entries-grid',
            'dropupId'    => 'surveymenuEntriesListAction',
            'dropUpText'  => gT('Selected menu entry/entries...'),
            'aActions'    => $buttons
    ));
?>
