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
            'iconClasses' => 'text-danger fa fa-trash',
            'text'        =>  gT('Delete'),
            'grid-reload' => 'yes',
            'on-success'  => "(function(result) { LS.AjaxHelper.onSuccess(result); })",

            // Modal
            'actionType'    => 'modal',
            'modalType'     => 'yes-no',
            'keepopen'      => 'no',
            'sModalTitle'   => gT('Confirm'),
            'htmlModalBody' => gT('Are you sure?'),
            'aCustomDatas'  => array()
        ),
    )
));

?>
