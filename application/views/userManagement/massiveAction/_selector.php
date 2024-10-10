<!-- Rendering massive action widget -->
<?php

$aActionsArray = array(
    'pk'          => 'selectedUser',
    'gridid'      => 'usermanagement--identity-gridPanel',
    'dropupId'    => 'usermanagement--actions',
    'dropUpText'  => gT('Selected user(s)...'),

    'aActions'    => array(

        // Delete
        array(
            'type'          => 'action',
            'action'        => 'delete',
            'url'           =>  App()->createUrl('userManagement/deleteMultiple'),
            'iconClasses'   => 'ri-delete-bin-fill text-danger',
            'text'          =>  gT('Delete'),
            'grid-reload'   => 'yes',
            'actionType'    => 'modal',
            'modalType'     => 'cancel-delete',
            'keepopen'      => 'yes',
            'showSelected'  => 'yes',
            'selectedUrl'   => App()->createUrl('userManagement/renderSelectedItems/'),
            'sModalTitle'   => gT('Delete user'),
            'htmlModalBody' => gT('Are you sure you want to delete the selected user?'),
        ),
        // ResendLoginData
        array(
            'type'          => 'action',
            'action'        => 'resendlogindata',
            'url'           =>  App()->createUrl('userManagement/batchSendAndResetLoginData'),
            'iconClasses'   => 'text-success ri-refresh-line',
            'text'          =>  gT('Resend login data'),
            'grid-reload'   => 'yes',
            'actionType'    => 'modal',
            'modalType'     => 'cancel-resend',
            'keepopen'      => 'yes',
            'showSelected'  => 'yes',
            'selectedUrl'   => App()->createUrl('userManagement/renderSelectedItems/'),
            'sModalTitle'   => gT('Resend login data'),
            'htmlModalBody' => gT('Are you sure you want to reset and resend selected users login data?'),
        ),
        // Mass EditnderPartial('/userManagement/massiveAction/_updatepermissions', [], true)
        array(
            'type'              => 'action',
            'action'            => 'batchPermissions',
            'url'               => App()->createUrl('userManagement/batchPermissions'),
            'iconClasses'       => 'ri-lock-unlock-fill',
            'text'              => gT('Edit permissions'),
            'grid-reload'       => 'yes',
            //modal
            'actionType'        => 'modal',
            'modalType'         => 'cancel-apply',
            'largeModalView'    => true,
            'keepopen'          => 'yes',
            'showSelected'      => 'yes',
            'selectedUrl'       => App()->createUrl('userManagement/renderSelectedItems/'),
            'sModalTitle'       => gT('Edit permissions'),
            //'htmlFooterButtons' => [],
            'htmlModalBody'     => App()->getController()->renderPartial('/userManagement/massiveAction/_updatepermissions', [], true)
        ),
        array(
            'type'              => 'action',
            'id'                => 'edit-status',
            'action'            => 'batchStatus',
            'url'               => App()->createUrl('userManagement/batchStatus'),
            'iconClasses'       => 'ri-user-follow-fill',
            'text'              => gT('Edit status'),
            'grid-reload'       => 'yes',
            //modal
            'actionType'        => 'modal',
            'modalType'         => 'cancel-apply',
            'largeModalView'    => true,
            'keepopen'          => 'yes',
            'showSelected'      => 'yes',
            'selectedUrl'       => App()->createUrl('userManagement/renderSelectedItems/'),
            'sModalTitle'       => gT('Edit status'),
            //'htmlFooterButtons' => [],
            'htmlModalBody'     => App()->getController()->renderPartial('/userManagement/massiveAction/_updatestatus', [], true)
        ),
    ),
);

if (Permission::model()->hasGlobalPermission('users', 'update')) {
    // Mass Edit -> roles only for superadmins
    $aActionsArray['aActions'][] = array(
        'type'          => 'action',
        'action'        => 'batchaddtogroup',
        'url'           => App()->createUrl('userManagement/batchAddGroup'),
        'iconClasses'   => 'ri-group-fill',
        'text'          => gT('Add to user group'),
        'grid-reload'   => 'yes',
        //modal
        'actionType'    => 'modal',
        'modalType'     => 'cancel-add',
        'keepopen'      => 'yes',
        'showSelected'  => 'yes',
        'selectedUrl'   => App()->createUrl('userManagement/renderSelectedItems/'),
        'sModalTitle'   => gT('Add to user group'),
        'htmlModalBody' => App()->getController()->renderPartial('/userManagement/massiveAction/_addtousergroup', [], true)
    );
}

if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
    // Mass Edit -> roles only for superadmins
    $aActionsArray['aActions'][] = array(
        'type'              => 'action',
        'action'            => 'batchaddrole',
        'url'               => App()->createUrl('userManagement/batchApplyRoles'),
        'iconClasses'       => 'ri-profile-line',
        'text'              => gT('Add role'),
        'grid-reload'       => 'yes',
        //modal
        'actionType'        => 'modal',
        'modalType'         => 'cancel-add',
        'keepopen'          => 'yes',
        'showSelected'      => 'yes',
        'selectedUrl'       => App()->createUrl('userManagement/renderSelectedItems/'),
        'sModalTitle'       => gT('Add role'),
        'htmlFooterButtons' => [],
        'htmlModalBody'     => App()->getController()->renderPartial('/userManagement/massiveAction/_updaterole', [], true)
    );
}


$this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', $aActionsArray);
