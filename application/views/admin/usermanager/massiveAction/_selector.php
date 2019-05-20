<!-- Rendering massive action widget -->
<?php

$aActionsArray = [];



$this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', array(
    'pk'          => 'selectedUser',
    'gridid'      => 'usermanagement--identity-gridPanel',
    'dropupId'    => 'usermanagement--actions',
    'dropUpText'  => gT('Selected users(s)...'),

    'aActions'    => array(
        
        // Delete
        array(
            'type'        => 'action',
            'action'      => 'delete',
            'url'         =>  App()->createUrl('/admin/usermanagement/sa/batchDelete'),
            'iconClasses' => 'text-danger fa fa-trash',
            'text'        =>  gT('Delete'),
            'grid-reload' => 'yes',
            'actionType'    => 'modal',
            'modalType'     => 'yes-no',
            'keepopen'      => 'yes',
            'sModalTitle'   => gT('Delete user'),
            'htmlModalBody' => gT('Are you sure you want to delete the selected user?'),
        ),
        // Mass Edit
        array(
            'type' => 'action',
            'action' => 'batchPermissions',
            'url' => App()->createUrl('/admin/usermanagement/sa/batchPermissions'),
            'iconClasses' => 'fa fa-unlock',
            'text' => gT('Batch edit permissions'),
            'grid-reload' => 'yes',
            //modal
            'actionType' => 'modal',
            'modalType'     => 'yes-no',
            'keepopen'      => 'yes',
            'yes'           => gT('Apply'),
            'no'            => gT('Cancel'),
            'sModalTitle'   => gT('Batch change permissions'),
            'htmlModalBody' => App()->getController()->renderPartial('/admin/usermanager/massiveAction/_updatepermissions', [], true)
        ),
    )
));

?>
