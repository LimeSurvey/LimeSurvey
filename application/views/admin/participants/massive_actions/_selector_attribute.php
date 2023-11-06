<!-- Rendering massive action widget for CPDB attribute list -->
<?php

$this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', array(
    'pk'          => 'selectedAttributeNames',
    'gridid'      => 'list_attributes',
    'dropupId'    => 'tokenListActions',
    'dropUpText'  => gT('Selected attribute(s)...'),

    'aActions'    => array(
        // Delete
        array(
            // li element
            'type'        => 'action',
            'action'      => 'delete',
            'url'         =>  App()->createUrl('/admin/participants/sa/deleteAttributes'),
            'iconClasses' => 'ri-delete-bin-fill text-danger',
            'text'        =>  gT('Delete'),
            'grid-reload' => 'yes',
            'on-success'  => "(function(result) { LS.AjaxHelper.onSuccess(result); })",

            // Modal
            'actionType'    => 'modal',
            'modalType'     => 'cancel-delete',
            'keepopen'      => 'no',
            'sModalTitle'   => gT('Delete'),
            'htmlModalBody' => gT('Do you really want to delete this attribute?'),
            'aCustomDatas'  => array()
        ),
    )
));

?>
