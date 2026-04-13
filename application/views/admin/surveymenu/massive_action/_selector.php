<?php
/**
 * Render the selector for surveys massive actions.
 *
 */
?>


<!-- Rendering massive action widget -->
<?php
    $buttons = array();
    if (Permission::model()->hasGlobalPermission('settings','update')) {
        // Delete
        $buttons[] = array(
            // li element
            'type'        => 'action',
            'action'      => 'delete',
            'url'         =>  App()->createUrl('/admin/menus/sa/massDelete/'),
            'iconClasses' => 'ri-delete-bin-fill text-danger',
            'text'        =>  gT('Delete'),
            'grid-reload' => 'yes',

            // modal
            'actionType'    => 'modal',
            'modalType'     => 'cancel-delete',
            'keepopen'      => 'no',
            'sModalTitle'   => gT('Delete menus'),
            'htmlModalBody' => gT('Are you sure you want to delete the selected menus and all related submenus and entries?'),
            'aCustomDatas'  => array(),
        );
    }

    if (Permission::model()->hasGlobalPermission('settings', 'update')) {
        // Download ZIP archive of file upload question types
        $buttons[] = array(
            'type' => 'action',
            'action' => 'batchEdit',
            'url' => App()->createUrl('/admin/menus/sa/batchEdit/'),
            'iconClasses' => 'ri-download-fill',
            'text' => gT('Batch edit'),
            'grid-reload' => 'yes',
            //modal
            'actionType' => 'modal',
            'modalType'     => 'cancel-save',
            'keepopen'      => 'yes',
            'yes'           => gT('Save'),
            'no'            => gT('Cancel'),
            'sModalTitle'   => gT('Batch edit the menus'),
            'htmlModalBody' => $this->renderPartial('./surveymenu/massive_action/_update', [], true)
        );

    }

    $this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', array(
            'pk'          => 'id',
            'gridid'      => 'surveymenu-grid',
            'dropupId'    => 'surveymenuListAction',
            'dropUpText'  => gT('Selected menu(s)...'),
            'aActions'    => $buttons
    ));
?>
